<?php

namespace App\Http\Controllers;

use App\Enum\InquiryResponseType;
use App\Enum\InquiryStatusType;
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
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class WarrantyController extends Controller
{
    public function inquire(InquiryWarrantyRequest $request)
    {
        $data = $request->validated();

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
            'attachments' => $attachmentPaths
        ]);

        return back()->with('success', 'Inquiry Submitted');
    }

    /**
     * @param string serial num of the product from the request uri wildcard
     * 
     * Display the specified resource.
     */
    public function show(string $serial)
    {
        // get the serial number of the product from the query string
        $warrantyClaim = Warranty::where('serial_number', $serial)->where('is_claimed', false)->firstORFail();

        // throw 403 that warranty is already claim
        if ($warrantyClaim->is_claimed) {
            abort(403, 'Warranty already claimed.');
        }

        // guard for the warranty expiration
        if (now()->greaterThan($warrantyClaim->expiry_date)) {
            abort(403, 'Warranty already expired.');
        }

        // add session for claiming the warranty
        session(['claim_warranty' => $warrantyClaim->id]);

        return Inertia::render('Auth/Register', [
            'warranty' => $warrantyClaim,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarrantyRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first('id');

        // get the product for the warranty duration
        $product = Product::findOrFail($data['product_id']);

        // PARSE THE DATE
        $purchaseDate = Carbon::parse($request->purchase_date);
        $expiryDate = $purchaseDate->copy()->addMonths($product->warranty_duration);

        $warrantyPayload = [
            'product_id' => $product->id,
            'serial_number' => $data['serial_number'],
            'purchase_date' => $purchaseDate,
            'expiry_date' => $expiryDate,
            'user_id' => $user?->id,
            'status' => $user ? WarrantyStatusType::ACTIVE : WarrantyStatusType::PENDING,
            'is_claimed' => (bool)$user
        ];

        if ($user) {
            $warrantyPayload['user_id'] = $user['id'];
        }

        $warranty = Warranty::create($warrantyPayload);

        if (! $user) {
            // assigned a registration url for claim and email to the customer
            $registrationLink = URL::temporarySignedRoute('customer.claim', $warranty->expiry_date, ['serial' => $warranty->serial_number]);

            $warranty->load('product');

            // send an invitation mail to customer account registration with the created url with hash for security purposes
            Mail::to($request->email)->send(new WarrantyInvitation($warranty, $request->email, $registrationLink));
        }

        return redirect('register-warranty')->with('status', 'Warranty created and email sent!');
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

        // when the tech or customer reply to the inquiry update the updated_at in the WarrantyInquiry
        $inquiries->warrantyInquiries->touch();

        broadcast(new InquiryResponseSent($inquiries))->toOthers();

        return back()->with('success', 'Inquiry Response Submitted');
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
}
