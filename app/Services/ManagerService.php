<?php

namespace App\Services;

use App\Enum\InquiryStatusType;
use App\Enum\WarrantyStatusType;
use App\Formatter\InquiryMessageFormatter;
use App\Http\Resources\PendingInquiryResource;
use App\Http\Resources\Warranty\WarrantyInquiries as ResourcesWarrantyInquiries;
use App\Http\Resources\Warranty\WarrantyResource;
use App\Models\User;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Inquiry\InquiryRepositoryInterface;
use App\Repositories\InquiryResponse\InquiryResponseRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Warranty\WarrantyRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class ManagerService
{
    public function __construct(
        private readonly WarrantyRepositoryInterface $warrantyRepository,
        private readonly InquiryRepositoryInterface $inquiryRepository,
        private readonly InquiryResponseRepositoryInterface $inquiryResponseRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        protected InquiryMessageFormatter $messageFormatter,
    ) {}

    public function getDashboardData(): array
    {
        return [
            'stats' => [
                'activeWarranty' => $this->warrantyRepository->countActiveGlobal(),
                'totalCustomer' => $this->userRepository->countCustomers(),
                'openInquiries' => $this->inquiryRepository->countOpenGlobal(),
                'unreadMessages' => $this->inquiryResponseRepository->countUnreadGlobal(),
            ],
            'chart' => $this->getWarrantyChartData(),
            'mostReportedProducts' => $this->productRepository->mostReported(5),
            'latestInquiries' => $this->inquiryRepository->getLatestWithWarrantySummary(5),
            'pendingInquiries' => PendingInquiryResource::collection($this->inquiryRepository->getLatestWithWarrantyProduct(5)),
        ];
    }

    public function getRegisterData(): array
    {
        return [
            'products' => $this->productRepository->getAllWithCategoryForRegistration(),
            'categories' => $this->categoryRepository->getForForm(),
        ];
    }

    public function getWarrantyInquiriesData(array $filters): array
    {
        $warrantyInquiries = $this->inquiryRepository->paginateForManager($filters)
            ->through(fn ($item) => new ResourcesWarrantyInquiries($item));

        return [
            'warrantyInquiries' => $warrantyInquiries,
            'select' => InquiryStatusType::options(),
        ];
    }

    public function getInquiryResponseData(int $id): array
    {
        $inquiry = $this->inquiryRepository->findWithFullRelations($id);

        $messages = $this->messageFormatter->format(collect([$inquiry]));

        $status = $inquiry->status instanceof InquiryStatusType
            ? $inquiry->status
            : InquiryStatusType::from($inquiry->status);

        $inquiry->is_done = $status->isFinal();

        return [
            'inquiry' => $inquiry,
            'messages' => $messages,
        ];
    }

    public function getWarrantiesData(array $filters): array
    {
        $warranties = $this->warrantyRepository->paginateForManager($filters)
            ->through(fn ($item) => new WarrantyResource($item));

        return [
            'warranties' => $warranties,
            'select' => WarrantyStatusType::options(),
        ];
    }

    public function getShowWarrantyData(string $id): array
    {
        $warranty = $this->warrantyRepository->findWithUserAndProduct($id);

        $history = $this->inquiryRepository->getByWarrantyId($warranty->id);

        return [
            'warranty' => $warranty,
            'history' => $history,
        ];
    }

    public function getCustomersData(array $filters): array
    {
        return [
            'customers' => $this->userRepository->paginateCustomers($filters),
        ];
    }

    public function getStaffAccountsData(array $filters): array
    {
        return [
            'users' => $this->userRepository->getStaffAndTechnicians($filters),
            'filters' => [
                'search' => $filters['search'] ?? null,
            ],
        ];
    }

    public function createStaff(array $data): User
    {
        return $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }

    public function updateStaffRole(User $user, string $role): User
    {
        return $this->userRepository->updateRole($user, $role);
    }

    public function deleteStaff(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    /**
     * Report data shared between the Inertia reports page and the PDF export.
     */
    public function getReportsData(array $filters): array
    {
        $allowedPeriod = [7, 12, 30];
        $period = $filters['period'] ?? '12';

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

        $rawData = $this->inquiryRepository->getInquiryCountsGroupedByPeriod($interval, $format);

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

        $mostReportedProducts = $this->productRepository->mostReportedInPeriod($interval, 8);

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
                'activeWarranty' => $this->warrantyRepository->countActiveGlobal(),
                'warrantyClaimCount' => $this->inquiryRepository->countSince($interval),
                'resolvedInquiry' => $this->inquiryRepository->countSinceWithStatus(
                    $interval,
                    [InquiryStatusType::RESOLVED, InquiryStatusType::REPLACED]
                ),
            ],
            'chartsData' => $datas,
            'nearExpiryWarranties' => $this->warrantyRepository->getNearExpiryWithUserAndProduct(),
            'selectedPeriod' => $period,
            'periodLabel' => $periodLabel
        ];
    }

    /**
     * Get the active, expired, near_expiry 12 month interval chart data.
     */
    private function getWarrantyChartData(): array
    {
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

        $data = $this->warrantyRepository->getChartRawData(now()->subMonths(11)->startOfMonth())
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
}
