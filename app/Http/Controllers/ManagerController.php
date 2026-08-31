<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\ReportsRequest;
use App\Http\Requests\Staff\StaffAccountRequest;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRoleRequest;
use App\Http\Requests\Warranty\Search\WarrantiesRequest;
use App\Http\Requests\Warranty\Search\WarrantyInquiriesRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use App\Services\ManagerService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    public function __construct(private readonly ManagerService $managerService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Manager/Dashboard', $this->managerService->getDashboardData());
    }

    /**
     * Registration of warranty
     */
    public function register(): Response
    {
        return Inertia::render('Manager/Register', $this->managerService->getRegisterData());
    }

    /**
     * Warranty Inquiries list of the customers
     */
    public function warrantyInquiries(WarrantyInquiriesRequest $request): Response
    {
        return Inertia::render('Manager/WarrantyInquiries', $this->managerService->getWarrantyInquiriesData($request->validated()));
    }

    /**
     * Showing inqury chats
     */
    public function inquiryResponse(int $id): Response
    {
        return Inertia::render('Manager/InquiryResponse', $this->managerService->getInquiryResponseData($id));
    }

    /**
     * Show all warranties
     */
    public function warranties(WarrantiesRequest $request): Response
    {
        return Inertia::render('Manager/Warranties', $this->managerService->getWarrantiesData($request->validated()));
    }

    public function showWarranty(string $id)
    {
        return Inertia::render('Manager/ShowWarranty', $this->managerService->getShowWarrantyData($id));
    }

    /**
     * List of customers
     */
    public function customers(CustomerRequest $request): Response
    {
        return Inertia::render('Manager/Customers', $this->managerService->getCustomersData($request->validated()));
    }

    /**
     * Reports for product and inquiries count
     */
    public function reports(ReportsRequest $request): Response
    {
        return Inertia::render('Manager/Reports', ["reports" => $this->managerService->getReportsData($request->validated())]);
    }

    /**
     * PDF Report download
     */
    public function generateReport(ReportsRequest $request)
    {
        $data = $this->managerService->getReportsData($request->validated());

        $pdf = Pdf::loadView('warranty-report', $data)->setPaper('letter', 'portrait');
        return $pdf->download("warranty-report-{$data['periodLabel']}.pdf");
    }

    public function staffAccounts(StaffAccountRequest $request): Response
    {
        return Inertia::render('Manager/StaffAccounts', $this->managerService->getStaffAccountsData($request->validated()));
    }

    public function profile(): Response
    {
        return Inertia::render('Manager/Profile');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStaffRequest $request)
    {
        $user = $this->managerService->createStaff($request->validated());

        return redirect()->back()->with('success', ucfirst($user->role->value) . ' created successfully');
    }

    public function updateRole(UpdateStaffRoleRequest $request, User $user)
    {
        $this->managerService->updateStaffRole($user, $request->validated('role'));

        return back()->with('success', 'Staff role updated successfully.');
    }

    public function destroyStaff(User $user)
    {
        // only admin
        if (Auth::user()->role !== UserRole::ADMIN) {
            return back()->with('error', 'Unauthorized action. Only admins can remove team members.');
        }

        // prevent deleting itself
        if ($user->id === Auth::user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // prevent deleting the admin
        if ($user->role === UserRole::ADMIN) {
            return back()->with('error', 'Administrative accounts cannot be deleted here.');
        }

        $this->managerService->deleteStaff($user);

        return back()->with('success', 'Staff account removed.');
    }
}
