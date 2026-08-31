<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inquiry\InquiryListRequest;
use App\Http\Requests\Warranty\Search\WarrantyListRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}

    public function index(): Response
    {
        return Inertia::render('Customer/Home', $this->customerService->getHomeData(Auth::id()));
    }

    public function warrantyList(WarrantyListRequest $request): Response
    {
        return Inertia::render('Customer/Warranties', $this->customerService->getWarrantyListData(Auth::id(), $request->validated()));
    }

    public function inquiries(InquiryListRequest $request): Response
    {
        return Inertia::render('Customer/Inquiries', $this->customerService->getInquiriesData(Auth::id(), $request->validated()));
    }

    public function history(): Response
    {
        return Inertia::render('Customer/History', $this->customerService->getHistoryData(Auth::id()));
    }

    public function show(Request $request, string $id): Response
    {
        return Inertia::render('Customer/Show', $this->customerService->getWarrantyShowData(Auth::id(), $id, $request->query('tab', 'records')));
    }

    /**
     * Show specific inquiry
     */
    public function showInquiry(Request $request, string $id): Response
    {
        return Inertia::render('Customer/Inquiry', $this->customerService->getInquiryShowData(Auth::id(), $id, $request->query('tab', 'messages')));
    }
}
