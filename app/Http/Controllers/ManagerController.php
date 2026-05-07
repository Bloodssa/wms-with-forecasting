<?php

namespace App\Http\Controllers;

use App\Enum\InquiryStatusType;
use App\Enum\UserRole;
use App\Enum\WarrantyStatusType;
use App\Http\Resources\PendingInquiryResource;
use App\Http\Resources\Warranty\WarrantyInfoResource;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Resources\Warranty\WarrantyInquiries as ResourcesWarrantyInquiries;
use App\Http\Resources\Warranty\WarrantyResource;
use App\Models\Category;
use App\Models\InquiryResponse;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use App\Models\WarrantyInquiries;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Manager/Dashboard', [
            'stats' => [
                'activeWarranty' => Warranty::isActive()->count(),
                'totalCustomer' => User::where('role', UserRole::CUSTOMER)->count(),
                'openInquiries' => WarrantyInquiries::where('status', InquiryStatusType::OPEN)->count(),
                'unreadMessages' => InquiryResponse::whereNull('read_at')->count()
            ],
            'chart' => $this->getWarrantyChartData(),
            'mostReportedProducts' => $this->mostReportedProduct(),
            'latestInquiries' => WarrantyInquiries::query()
                ->select(['id', 'user_id', 'warranty_id', 'status'])
                ->with(['user:id,name', 'warranty:id,product_id', 'warranty.product:id,name'])->latest()->take(5)->get(),
            'pendingInquiries' => PendingInquiryResource::collection(WarrantyInquiries::with(['user:id,name,email', 'warranty.product:id,name'])->orderBy('created_at', 'desc')->take(5)->get())
        ]);
    }

    /**
     * Registration of warranty
     */
    public function register(): Response
    {
        return Inertia::render('Manager/Register', [
            'products' => Product::query()
                ->select(['id', 'category_id', 'price', 'name', 'brand', 'product_image_url'])
                ->with('category:id,name')
                ->get(),
            'categories' => Category::pluck('name')
        ]);
    }

    /**
     * Warranty Inquiries list of the customers
     */
    public function warrantyInquiries(Request $request): Response
    {
        $warrantyInquiries = WarrantyInquiries::query()
            ->select(['id', 'user_id', 'warranty_id', 'message', 'status', 'created_at'])
            ->with('user:id,name,email', 'warranty:id,product_id,serial_number', 'warranty.product:id,name')
            ->withCount([
                'responses as unread_messages_count' => function ($q) {
                    $q->whereNull('read_at')
                        ->where('user_id', '!=', Auth::id());
                }
            ])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warranty', function ($q2) use ($search) {
                        $q2->where('serial_number', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($q3) use ($search) {
                                $q3->where('name', 'like', "%{$search}%");
                            });
                    })->orWhereHas('user', function ($q4) use ($search) {
                        $q4->where('name', 'like', "%{$search}%");
                    });
                });
            })
            ->orderByDesc('unread_messages_count')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->through(fn($item) => new ResourcesWarrantyInquiries($item));

        return Inertia::render('Manager/WarrantyInquiries', [
            'warrantyInquiries' => $warrantyInquiries,
            'select' => InquiryStatusType::options()
        ]);
    }

    /**
     * Showing inqury chats
     */
    public function inquiryResponse(int $id): Response
    {
        $inquiry = WarrantyInquiries::with(['warranty.product.category', 'user', 'responses.user'])
            ->findOrFail($id);

        // collect and combine inquiry and messages
        $messages = $this->inquiryMessages(collect([$inquiry]));
        // dd($messages);

        $status = $inquiry->status instanceof InquiryStatusType ? $inquiry->status : InquiryStatusType::from($inquiry->status);

        $inquiry->is_done = $status->isFinal(); // attch the bool if its done

        return Inertia::render('Manager/InquiryResponse', [
            'inquiry' => $inquiry,
            'messages' => $messages
        ]);
    }

    /**
     * Show all warranties
     */
    public function warranties(Request $request): Response
    {
        $warranties = Warranty::query()
            ->select(['id', 'product_id', 'user_id', 'serial_number', 'is_claimed', 'claim_email', 'status', 'purchase_date', 'expiry_date'])
            ->with(['product:id,name,product_image_url', 'user:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q1) use ($search) {
                            $q1->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->through(fn($item) => new WarrantyResource($item));


        return Inertia::render('Manager/Warranties', [
            'warranties' => $warranties,
            'select' => WarrantyStatusType::options()
        ]);
    }

    public function showWarranty(string $id)
    {
        $warranty = Warranty::with(['user', 'product.category'])
            ->where('id', $id)
            ->firstOrFail();

        $history = WarrantyInquiries::with('user')->where('warranty_id', $warranty->id)->get();

        return Inertia::render('Manager/ShowWarranty', [
            'warranty' => $warranty,
            'history' => $history
        ]);
    }

    /**
     * List f customers
     */
    public function customers(Request $request): Response
    {
        $customers = User::query()
            ->select(['name', 'email'])
            ->where('role', '=', 'customer')->withCount([
                'warranties as active_warranties_count' => function ($query) {
                    $query->whereIn('status', [WarrantyStatusType::ACTIVE, WarrantyStatusType::NEAR_EXPIRY]);
                },
                'warranties as expired_warranties_count' => function ($query) {
                    $query->where('status', WarrantyStatusType::EXPIRED);
                }
            ])
            ->withMax('inquiries as last_inquiry_status', 'status') // max or latest created_at
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return Inertia::render('Manager/Customers', [
            'customers' => $customers
        ]);
    }

    /**
     * Reports for product and inquiries count
     */
    public function reports(Request $request): Response
    {
        $reports = $this->reportsData($request);

        return Inertia::render('Manager/Reports', [
            'reports' => $reports
        ]);
    }

    /**
     * PDF Report download
     */
    public function generateReport(Request $request)
    {
        $data = $this->reportsData($request);

        $pdf = Pdf::loadView('warranty-report', $data);
        return $pdf->download("warranty-report-{$data['periodLabel']}.pdf");
    }

    public function staffAccounts(Request $request): Response
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->whereIn('role', [UserRole::STAFF, UserRole::TECHNICIAN])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->latest()
            ->get();

        return Inertia::render('Manager/StaffAccounts', [
            'users' => $users,
            'filters' => [
                'search' => $request->search
            ]
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('Manager/Profile');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', Rules\Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)]
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return redirect()->back()->with('success', ucfirst($user->role->value) . ' created successfully');
    }

    public function updateRole(Request $request, User $user)
    {
        if (Auth::user()->role !== UserRole::ADMIN) {
            return back()->with('error', 'Unauthorized action. Only admins can update roles.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own administrative role.');
        }

        $request->validate([
            'role' => ['required', Rule::enum(UserRole::class), Rule::in([UserRole::STAFF, UserRole::TECHNICIAN])]
        ]);

        $user->update(['role' => $request->role]);

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

        $user->delete();

        return back()->with('success', 'Staff account removed.');
    }


    /**
     * Helpers
     */

    /**
     * Get the expire, near_expiry, expired count per month with 12 month interval
     */
    private function getWarrantyChartData(): array
    {
        // get the active, expired, near_expiry 12 months interval
        $months = collect(range(11, 0))->mapWithKeys(function ($i) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');

            return [$key => [
                'label' => $date->format('M'),
                'active' => 0,
                'near_expiry' => 0,
                'expired' => 0,
            ]];
        });

        $data = Warranty::selectRaw("
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(status IN ('active', 'pending')) as active,
            SUM(status = 'near-expiry') as near_expiry,
            SUM(status = 'expired') as expired
        ")
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->groupBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return [
                    'active' => (int) $item->active,
                    'near_expiry' => (int) $item->near_expiry,
                    'expired' => (int) $item->expired,
                ];
            });

        $finalData = $months->map(function ($default, $monthKey) use ($data) {
            if (isset($data[$monthKey])) {
                return array_merge($default, $data[$monthKey]);
            }
            return $default;
        });

        return [
            'labels' => $finalData->pluck('label')->values(),
            'active' => $finalData->pluck('active')->values(),
            'nearExpiry' => $finalData->pluck('near_expiry')->values(),
            'expired' => $finalData->pluck('expired')->values(),
        ];
    }

    private function mostReportedProduct(): Collection
    {
        return DB::table('products as p')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->join('warranties as w', 'p.id', '=', 'w.product_id')
            ->join('warranty_inquiries as wi', 'w.id', '=', 'wi.warranty_id')
            ->selectRaw('p.name, p.product_image_url, c.name as category_name, COUNT(wi.id) as total_inquiries')
            ->groupBy('p.id', 'p.name', 'c.name', 'product_image_url')
            ->orderByDesc('total_inquiries')
            ->limit(5)
            ->get();
    }

    /**
     * Helper function for pdf and blade reports 
     */
    private function reportsData(Request $request)
    {
        $allowedPeriod = [7, 12, 30];
        $period = $request->get('period', '12');

        // prevent error when user tries to put a string in query param
        if (!in_array($period, $allowedPeriod)) {
            $period = 12;
        }

        $interval = match ($period) {
            '7' => now()->subDays(6)->startOfDay(),
            '30' => now()->subDays(29)->startOfDay(),
            '12' => now()->subMonths(11)->startOfMonth(),
            default => now()->subMonths(11)->startOfMonth()
        };
        $format = ($period == '12') ? '%Y-%m' : '%Y-%m-%d';

        $rawData = WarrantyInquiries::selectRaw("
            DATE_FORMAT(created_at, '{$format}') as period,
            COUNT(*) as total
        ")
            ->where('created_at', '>=', $interval)
            ->groupBy('period')
            ->pluck('total', 'period');

        $months = collect();

        if ($period == 12) {
            $start = now()->startOfMonth();

            for ($i = 11; $i >= 0; $i--) {
                $date = $start->copy()->subMonths($i);

                $key = $date->format('Y-m');
                $label = $date->format('M');

                $months->push([
                    'month' => $label,
                    'total' => $rawData[$key] ?? 0
                ]);
            }
        } else {
            $start = now();

            for ($i = $period - 1; $i >= 0; $i--) {
                $date = $start->copy()->subDays($i);

                $key = $date->format('Y-m-d');
                $label = $date->format('d M');

                $months->push([
                    'month' => $label,
                    'total' => $rawData[$key] ?? 0
                ]);
            }
        }

        $mostReportedProducts = DB::table('products as p')
            ->leftJoin('warranties as w', 'w.product_id', '=', 'p.id')
            ->leftJoin('warranty_inquiries as wi', function ($join) use ($interval) {
                $join->on('wi.warranty_id', '=', 'w.id')
                    ->where('wi.created_at', '>=', $interval);
            })
            ->select('p.name', DB::raw('COUNT(wi.id) as inquiries_count'))
            ->groupBy('p.name')
            ->orderByDesc('inquiries_count')
            ->take(8)
            ->get();


        // merged charts data and casted into object for calling like a orm in blade
        $datas = (object)[
            'inquiries' => (object)[
                'labels' => $months->pluck('month'),
                'data' => $months->pluck('total'),
            ],
            'reportedProducts' => (object)[
                'labels' => $mostReportedProducts->pluck('name'),
                'data' => $mostReportedProducts->pluck('inquiries_count')
            ]
        ];

        $periodLabel = match ($period) {
            '7'  => 'last-7-days',
            '30' => 'last-30-days',
            '12' => 'last-12-months',
            default => 'last-12-months',
        };

        return [
            'stats' => [
                'activeWarranty' => Warranty::isActive()->count(),
                'warrantyClaimCount' => WarrantyInquiries::where('created_at', '>=', $interval)->count(),
                'resolvedInquiry' => WarrantyInquiries::whereIn('status', [InquiryStatusType::RESOLVED, InquiryStatusType::REPLACED])->where('created_at', '>=', $interval)->count(),
            ],
            'chartsData' => $datas,
            'nearExpiryWarranties' => Warranty::with('user', 'product')->where('status', WarrantyStatusType::NEAR_EXPIRY)->orderBy('created_at', 'desc')->get(),
            'selectedPeriod' => $period,
            'periodLabel' => $periodLabel
        ];
    }
}
