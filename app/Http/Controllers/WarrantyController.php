<?php

namespace App\Http\Controllers;

use App\Enum\InquiryResponseType;
use App\Enum\InquiryStatusType;
use App\Enum\UserRole;
use App\Enum\WarrantyStatusType;
use App\Events\InquiryResponseSent;
use App\Http\Requests\Warranty\InquiryWarrantyRequest;
use App\Http\Requests\Warranty\StoreWarrantyRequest;
use App\Http\Requests\Warranty\UpdateInquiryStatus;
use App\Mail\WarrantyInvitation;
use App\Models\InquiryResponse;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyInquiries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class WarrantyController extends Controller
{
    public function updateWarranty(Request $request, Warranty $warranty)
    {
        if (Auth::user()->role !== UserRole::ADMIN) {
            return Redirect::back()->with('error', 'Only admins can update warranties.');
        }

        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'max:255', Rule::unique('warranties', 'serial_number')->ignore($warranty->id)],
            'purchase_date' => ['required', 'date'],
            'expiry_date' => ['required', 'date', 'after_or_equal:purchase_date'],
            'status' => ['required', Rule::enum(WarrantyStatusType::class)],
        ]);

        $warranty->update($validated);

        return Redirect::back()->with('success', 'Warranty updated successfully.');
    }

    public function destroyWarranty(Warranty $warranty)
    {
        if (Auth::user()->role !== UserRole::ADMIN) {
            return Redirect::back()->with('error', 'Only admins can update warranties.');
        }

        $warranty->delete();

        return Redirect::back()->with('success', 'Warranty deleted successfully.');
    }

    /**
     * Archive warranty
     */
    public function archiveWarranty(Warranty $warranty)
    {
        $warranty->update([
            'status' => WarrantyStatusType::ARCHIVED,
            'archived_at' => now(),
        ]);

        return back()->with('success', 'Warranty archived.');
    }

    /**
     * Cancel archive
     */
    public function unarchiveWarranty(Warranty $warranty)
    {
        $warranty->archived_at = null;

        $warranty->update([
            'archived_at' => null,
            'status' => $this->computeStatus($warranty),
        ]);

        return back()->with('success', 'Warranty restored.');
    }

    public function inquire(InquiryWarrantyRequest $request)
    {
        $data = $request->validated();

        // guard if warranty expired then dont allow for inquiries
        $warranty = Warranty::findOrFail($data['warranty_id']);

        // if the user tries to inquire in other warranty or not his own warranty
        if ($warranty->user_id !== Auth::user()->id) {
            return back()->with('error', 'You are not allowed to access this warranty.');
        }

        if (now()->greaterThan($warranty->expiry_date)) {
            return back()->with('error', 'This warranty has already expired and cannot accept new inquiries.');
        }

        // handle the image path and it will be stored as a json
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('inquiries', 'public');
            }
        }

        $data['attachments'] = $attachmentPaths;

        $inquiries = WarrantyInquiries::create([
            'warranty_id' => $data['warranty_id'],
            'message' => $data['message'],
            'user_id' => Auth::id(),
            'status' => InquiryStatusType::OPEN,
            'attachments' => $attachmentPaths,
            'read_at' => null
        ]);

        return redirect()->route('inquiry.show', $inquiries->id)->with('success', 'Inquiry Submitted');
    }

    public function storeInquiry(string $id)
    {
        $warranty = Warranty::with('product.category')
            ->where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();

        // user does not have warranty
        if (! $warranty) {
            return back()->with('error', 'Unauthorized access to warranty.');
        }

        // warranty expired
        if (now()->greaterThan($warranty->expiry_date)) {
            return redirect()->route('inquiries')->with('error', 'This warranty has expired and cannot accept inquiries.');
        }

        // dont allow customer to send multiple inquiries
        // get the final statuses
        $finalStatuses = collect(InquiryStatusType::cases())
            ->filter(fn($status) => $status->isFinal())
            ->map(fn($status) => $status->value)
            ->toArray();

        $hasActiveInquiry = $warranty->inquiries()
            ->whereNotIn('status', $finalStatuses)
            ->exists();

        if ($hasActiveInquiry) {
            return redirect()->route('inquiries')->with('error', 'You already have an active inquiry for this product.');
        }

        return Inertia::render('Customer/Create', [
            'warranty' => $warranty
        ]);
    }

    /**
     * @param string serial num of the product from the request uri wildcard
     * 
     * Display the specified resource.
     */
    public function show(string $email)
    {
        // get the serial number of the product from the query string
        // incase customer miss the expiry date
        $warranties = Warranty::with('product')
            ->where('claim_email', $email)
            ->where('is_claimed', false)
            ->get();

        if ($warranties->isEmpty()) {
            abort(404, 'No claimable warranties found.');
        }

        // add session for claiming the warranty
        session([
            'claim_email' => $email,
            'claim_warranty_id' => $warranties->pluck('id')->toArray() // get all id to display in the registration products
        ]);

        return Inertia::render('Auth/Register', [
            'email' => $email,
            'warranties' => $warranties,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarrantyRequest $request)
    {
        $data = $request->validated();

        $createdWarranties = [];
        $user = null;

        DB::transaction(function () use ($data, &$user, &$createdWarranties) {

            $user = User::where('email', $data['claim_email'])->first();

            foreach ($data['multiple_products'] as $item) {

                $product = Product::findOrFail($item['product_id']);

                $purchaseDate = now();
                $expiryDate = $purchaseDate->copy()->addMonths($product->warranty_duration);

                $warranty = Warranty::create([
                    'user_id' => $user?->id,
                    'product_id' => $product->id,
                    'claim_email' => $data['claim_email'],
                    'serial_number' => $item['serial_number'],
                    'purchase_date' => $purchaseDate,
                    'purchase_price' => $item['price'],
                    'expiry_date' => $expiryDate,
                    'status' => $user ? WarrantyStatusType::ACTIVE : WarrantyStatusType::PENDING,
                    'is_claimed' => (bool) $user
                ]);

                $createdWarranties[] = $warranty->id;
            }
        });

        $warranties = Warranty::with('product')
            ->whereIn('id', $createdWarranties)
            ->get();

        Log::info('User Warranty Send mail: ' . $data['claim_email']);

        $registrationLink = URL::temporarySignedRoute(
            'customer.claim',
            now()->addDays(60),
            ['email' => $data['claim_email']]
        );

        Mail::to($data['claim_email'])->send(new WarrantyInvitation(
            $warranties,
            $data['claim_email'],
            $registrationLink
        ));

        return redirect()->route('register-warranty') // Use the name of your registration page route
            ->with([
                'success' => 'Warranty registered successfully!',
                'download_ids' => $createdWarranties
            ]);
        // return redirect('register-warranty')
        //     ->with('success', 'Warranty created and email sent!');
    }

    /**
     * Download invoice
     */
    public function downloadInvoice(Request $request)
    {
        $ids = $request->query('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['error' => 'No IDs provided'], 400);
        }

        $warranties = Warranty::with('product')->whereIn('id', $ids)->get();

        if ($warranties->isEmpty()) {
            return response()->json(['error' => 'No warranties found'], 404);
        }

        $pdf = Pdf::loadView('warranty-invoice', [
            'warranties' => $warranties,
            'email' => $warranties->first()->claim_email,
            'total' => $warranties->sum('purchase_price'),
            'date' => now()
        ]);

        return $pdf->stream('warranty-invoice.pdf');
        // return $pdf->download('warranty-invoice-' . now()->timestamp . '.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function response(Request $request)
    {
        // dd($request);
        $data = $request->validate([
            'warranty_inquiries_id' => ['required', 'numeric', 'exists:warranty_inquiries,id'],
            'message' => ['required', 'string'],
            'attachments.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120']
        ]);

        $data['user_id'] = Auth::user()->id;

        // handle the image path and it will be stored as a json
        $attachmentPaths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentPaths[] = $file->store('inquiries', 'public');
            }
        }

        $data['attachments'] = $attachmentPaths;

        $inquiries = InquiryResponse::create($data);

        // mark as unread
        $inquiries->warrantyInquiries->update([
            'read_at' => null
        ]);

        // when the tech or customer reply to the inquiry update the updated_at in the WarrantyInquiry
        // $inquiries->warrantyInquiries->touch();

        broadcast(new InquiryResponseSent($inquiries))->toOthers();

        return back();
    }

    // mark as read messages
    public function markRead(string $id)
    {
        $userId = Auth::id();

        $unreadExists = InquiryResponse::where('warranty_inquiries_id', $id)
            ->whereNull('read_at')
            ->where('user_id', '!=', $userId)
            ->exists();

        if ($unreadExists) {
            InquiryResponse::where('warranty_inquiries_id', $id)
                ->whereNull('read_at')
                ->where('user_id', '!=', $userId)
                ->update([
                    'read_at' => now()
                ]);

            WarrantyInquiries::where('id', $id)
                ->update([
                    'read_at' => now()
                ]);
        }

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInquiryStatus $request, string $id)
    {
        $data = $request->validated();

        // dd($request);
        $inquiry = WarrantyInquiries::findOrFail($id);
        $previousStatus = $inquiry->status;
        $newStatus = InquiryStatusType::from($data['status']);

        if (!$previousStatus->canTransitionTo($newStatus)) {
            return back()->with('error', "Invalid transition: You cannot move the status from " . $previousStatus->label() . " to " . $newStatus->label() . ".");
        }

        $inquiry->update([
            'status' => $data['status']
        ]);

        $resolvedMessage = $request->input('resolved_message');
        $message = $resolvedMessage ?? "Status changed from " . $previousStatus->label() . " to " . InquiryStatusType::from($data['status'])->label();
        $type = $resolvedMessage ? InquiryResponseType::SOLUTION : InquiryResponseType::UPDATES;

        $response = $inquiry->responses()->create([
            'user_id' => Auth::user()->id,
            'message' => $message,
            'type' => $type
        ]);

        broadcast(new InquiryResponseSent($response))->toOthers();

        return back()->with('success', 'Inquiry status updated successfully!');
    }

    /**
     * Customer cancel the inquiry
     */
    public function cancelInquiry(Request $request, string $id)
    {
        $request->validate([
            'message' => ['required', 'string']
        ]);

        $inquiry = WarrantyInquiries::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $inquiry) {
            return back()->with('error', 'Inquiry not found.');
        }

        $currentStatus = $inquiry->status;

        if ($currentStatus->isFinal()) {
            return back()->with('error', 'This inquiry can no longer be cancelled.');
        }

        $inquiry->update([
            'status' => InquiryStatusType::CLOSED
        ]);

        $inquiry->responses()->create([
            'user_id' => Auth::id(),
            'message' => "User cancelled inquiry: {$request->message}", // merge customer message
            'type' => InquiryResponseType::SOLUTION
        ]);

        return back()->with('success', 'Inquiry has been cancelled.');
    }

    /**
     * Session based notification read
     */
    public function markReadNotifications()
    {
        session([
            'notifications_read_at' => now()
        ]);

        return back();
    }

    public function claimWithSerialNumber(Request $request)
    {
        $request->validate([
            'serial_number' => ['required', 'string'],
            'purchase_email' => ['required', 'string']
        ]);

        $warranty = Warranty::where('serial_number', $request->serial_number)
            ->where('claim_email', $request->purchase_email)
            ->whereNull('user_id')
            ->first();

        if (!$warranty) {
            return back()->with('error', 'We could not find a pending warranty with this serial number and email combination.');
        }

        $warranty->update([
            'user_id' => Auth::id(),
            'status' => WarrantyStatusType::ACTIVE,
            'is_claimed' => true,
        ]);

        return back()->with('success', 'Warranty successfully claimed Product Name: ' . $warranty->product->name);
    }

    private function computeStatus(Warranty $warranty)
    {
        if ($warranty->archived_at) {
            return WarrantyStatusType::ARCHIVED;
        }

        $now = now();

        if ($warranty->expiry_date <= $now) {
            return WarrantyStatusType::EXPIRED;
        }

        if ($warranty->expiry_date <= $now->copy()->addMonth()) {
            return WarrantyStatusType::NEAR_EXPIRY;
        }

        return WarrantyStatusType::ACTIVE;
    }
}
