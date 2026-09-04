<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarrantyServiceRecord\StoreWarrantyServiceRecordRequest;
use App\Services\WarrantyService;
use Illuminate\Support\Facades\Auth;

class WarrantyServiceRecordController extends Controller
{
    public function __construct(protected WarrantyService $warrantyService) {}

    /**
     * Record an actual repair/service cost against a warranty inquiry.
     * Authorization is handled in StoreWarrantyServiceRecordRequest::authorize().
     */
    public function store(StoreWarrantyServiceRecordRequest $request, string $inquiry)
    {
        $this->warrantyService->recordServiceCost(
            (int) $inquiry,
            Auth::id(),
            $request->validated()
        );

        return back()->with('success', 'Service cost recorded.');
    }
}
