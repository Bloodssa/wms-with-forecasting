<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Exceptions\WarrantyOperationException;
use App\Http\Requests\Inquiry\CancelInquiryRequest;
use App\Http\Requests\InquiryResponse\StoreInquiryResponseRequest;
use App\Http\Requests\Warranty\ClaimSerialNumberRequest;
use App\Http\Requests\Inquiry\InquiryWarrantyRequest;
use App\Http\Requests\Warranty\StoreWarrantyRequest;
use App\Http\Requests\Inquiry\UpdateInquiryStatus;
use App\Http\Requests\Warranty\UpdateWarrantyRequest;
use App\Services\InquiryService;
use App\Models\Warranty;
use App\Services\WarrantyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Barryvdh\DomPDF\Facade\Pdf;

class WarrantyController extends Controller
{
    public function __construct(
        private readonly WarrantyService $warrantyService,
        private readonly InquiryService $inquiryService
    ) {}

    public function updateWarranty(UpdateWarrantyRequest $request, Warranty $warranty)
    {
        $this->warrantyService->updateWarranty($warranty, $request->validated());

        return Redirect::back()->with('success', 'Warranty updated successfully.');
    }

    public function destroyWarranty(Warranty $warranty)
    {
        if (Auth::user()->role !== UserRole::ADMIN) {
            return Redirect::back()->with('error', 'Only admins can update warranties.');
        }

        $this->warrantyService->destroyWarranty($warranty);

        return Redirect::back()->with('success', 'Warranty deleted successfully.');
    }

    /**
     * Archive warranty
     */
    public function archiveWarranty(Warranty $warranty)
    {
        $this->warrantyService->archiveWarranty($warranty);

        return back()->with('success', 'Warranty archived.');
    }

    /**
     * Cancel archive
     */
    public function unarchiveWarranty(Warranty $warranty)
    {
        $this->warrantyService->unarchiveWarranty($warranty);

        return back()->with('success', 'Warranty restored.');
    }

    public function inquire(InquiryWarrantyRequest $request)
    {
        try {
            $inquiry = $this->inquiryService->createInquiry($request->validated(), $request->file('attachments', []), Auth::id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('inquiry.show', $inquiry->id)->with('success', 'Inquiry Submitted');
    }

    public function storeInquiry(string $id)
    {
        try {
            $warranty = $this->warrantyService->getInquiryCreationContext($id, Auth::id());
        } catch (WarrantyOperationException $e) {
            return $e->redirectRoute()
                ? redirect()->route($e->redirectRoute())->with('error', $e->getMessage())
                : back()->with('error', $e->getMessage());
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
        $warranties = $this->warrantyService->getClaimableWarranties($email);

        if ($warranties->isEmpty()) {
            abort(404, 'No claimable warranties found.');
        }

        // add session for claiming the warranty
        session([
            'claim_email' => $email,
            'claim_warranty_id' => $warranties->pluck('id')->toArray()
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
        $result = $this->warrantyService->registerWarranties($request->validated());

        return redirect()->route('register-warranty')
            ->with([
                'success' => 'Warranty registered successfully!',
                'download_ids' => $result['createdIds']
            ]);
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

        try {
            $data = $this->warrantyService->getInvoiceData($ids);
        } catch (WarrantyOperationException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }

        $pdf = Pdf::loadView('warranty-invoice', $data);

        return $pdf->stream('warranty-invoice.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function response(StoreInquiryResponseRequest $request)
    {
        $this->warrantyService->createResponse($request->validated(), $request->file('attachments', []), Auth::id());

        return back();
    }

    // mark as read messages
    public function markRead(string $id)
    {
        $this->warrantyService->markInquiryRead($id, Auth::id());

        return back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInquiryStatus $request, string $id)
    {
        try {
            $this->warrantyService->transitionInquiryStatus($id, $request->validated()['status'], $request->input('resolved_message'), Auth::id());
        } catch (WarrantyOperationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Inquiry status updated successfully!');
    }

    /**
     * Customer cancel the inquiry
     */
    public function cancelInquiry(CancelInquiryRequest $request, string $id)
    {
        try {
            $this->warrantyService->cancelInquiry((int) $id, $request->validated()['message'], Auth::id());

            return back()->with('success', 'Inquiry has been cancelled.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Session based notification read
     */
    public function markReadNotifications()
    {
        session(['notifications_read_at' => now()]);

        return back();
    }

    public function claimWithSerialNumber(ClaimSerialNumberRequest $request)
    {
        try {
            $warranty = $this->warrantyService->claimBySerialAndEmail($request->validated('serial_number'), $request->validated('purchase_email'), Auth::id());
        } catch (WarrantyOperationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Warranty successfully claimed Product Name: ' . $warranty->product->name);
    }
}
