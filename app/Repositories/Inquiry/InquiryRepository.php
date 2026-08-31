<?php

namespace App\Repositories\Inquiry;

use App\Enum\InquiryStatusType;
use App\Models\WarrantyInquiries;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InquiryRepository implements InquiryRepositoryInterface
{
    public function getLatestWithWarrantyProduct(int $limit = 5): Collection
    {
        return WarrantyInquiries::with(['warranty', 'warranty.product:id,name'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getLatestWithWarrantySummary(int $limit = 5): Collection
    {
        return WarrantyInquiries::query()
            ->select(['id', 'warranty_id', 'status'])
            ->with(['warranty:id,user_id,product_id', 'warranty.user:id,name', 'warranty.product:id,name'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function paginateForManager(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        return WarrantyInquiries::query()
            ->select(['id', 'warranty_id', 'message', 'status', 'created_at'])
            ->with([
                'warranty:id,user_id,product_id,serial_number',
                'warranty.user:id,name,email',
                'warranty.product:id,name'
            ])
            ->withCount([
                'responses as unread_messages_count' => function ($q) {
                    $q->whereNull('read_at')
                        ->where('user_id', '!=', Auth::id());
                }
            ])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warranty', function ($q2) use ($search) {
                        $q2->where('serial_number', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($q3) use ($search) {
                                $q3->where('name', 'like', "%{$search}%");
                            });
                    })
                        ->orWhereHas('warranty.user', function ($q4) use ($search) {
                            $q4->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('unread_messages_count')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);
    }

    public function findWithFullRelations(int $id): WarrantyInquiries
    {
        return WarrantyInquiries::with(['warranty.product.category', 'warranty.user', 'responses.user'])
            ->findOrFail($id);
    }

    public function getInquiryCountsGroupedByPeriod(Carbon $since, string $format): Collection
    {
        return WarrantyInquiries::selectRaw("
            DATE_FORMAT(created_at, '{$format}') as period,
            COUNT(*) as total
        ")
            ->where('created_at', '>=', $since)
            ->groupBy('period')
            ->pluck('total', 'period');
    }

    public function countSince(Carbon $since): int
    {
        return WarrantyInquiries::where('created_at', '>=', $since)->count();
    }

    public function countOpenGlobal(): int
    {
        return WarrantyInquiries::where('status', InquiryStatusType::OPEN)->count();
    }

    public function countSinceWithStatus(Carbon $since, array $statuses): int
    {
        return WarrantyInquiries::whereIn('status', $statuses)
            ->where('created_at', '>=', $since)
            ->count();
    }

    public function countResolvedForUser(int $userId): int
    {
        return WarrantyInquiries::whereHas('warranty', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('status', InquiryStatusType::RESOLVED)
            ->count();
    }

    public function paginateForCustomer(int $userId, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        return WarrantyInquiries::query()
            ->select(['id', 'warranty_id', 'message', 'status', 'updated_at'])
            ->with([
                'warranty:id,product_id,status,serial_number',
                'warranty.product:id,category_id,brand,name,product_image_url',
                'warranty.product.category:id,name'
            ])
            ->withCount([
                'responses as unread_count' => function ($query) {
                    $query->whereNull('read_at')
                        ->where('user_id', '!=', Auth::id());
                }
            ])
            ->whereHas('warranty', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereIn('status', [InquiryStatusType::OPEN, InquiryStatusType::PENDING, InquiryStatusType::IN_PROGRESS])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('warranty', function ($q1) use ($search) {
                        $q1->where('serial_number', 'like', "%{$search}%");
                    })
                        ->orWhereHas('warranty.product', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->withMax('responses', 'created_at')
            ->orderByDesc('responses_max_created_at')
            ->paginate(10)
            ->withQueryString();
    }

    public function getAllWithWarrantyProductForUser(int $userId): Collection
    {
        return WarrantyInquiries::with('warranty.product')
            ->whereHas('warranty', fn($q) => $q->where('user_id', $userId))
            ->get();
    }

    public function getResolvedLikeWithWarrantyProductForUser(int $userId): Collection
    {
        return WarrantyInquiries::with('warranty.product')
            ->whereHas('warranty', fn($q) => $q->where('user_id', $userId))
            ->whereIn('status', ['resolved', 'replaced', 'closed'])
            ->get();
    }

    public function getByWarrantyId(string $warrantyId): Collection
    {
        return WarrantyInquiries::with('warranty.user')->where('warranty_id', $warrantyId)->get();
    }

    public function findLatestForWarranty(string $warrantyId, int $userId): ?WarrantyInquiries
    {
        return WarrantyInquiries::whereHas('warranty', fn($q) => $q->where('user_id', $userId))
            ->where('warranty_id', $warrantyId)
            ->latest()
            ->first();
    }

    public function findActiveForWarrantyExcluding(string $warrantyId, int $userId, array $excludedStatuses): ?WarrantyInquiries
    {
        return WarrantyInquiries::whereHas('warranty', fn($q) => $q->where('user_id', $userId))
            ->where('warranty_id', $warrantyId)
            ->whereNotIn('status', $excludedStatuses)
            ->latest()
            ->first();
    }

    public function findWithRelationsForCustomer(string $id, int $userId): WarrantyInquiries
    {
        return WarrantyInquiries::with([
            'warranty.product.category',
            'warranty.user',
            'responses.user'
        ])
            ->whereHas('warranty', fn($q) => $q->where('user_id', $userId))
            ->findOrFail($id);
    }

    public function hasActiveForWarranty(string $warrantyId, array $finalStatuses): bool
    {
        return WarrantyInquiries::where('warranty_id', $warrantyId)
            ->whereNotIn('status', $finalStatuses)
            ->exists();
    }

    public function markRead(string $id): void
    {
        WarrantyInquiries::where('id', $id)->update([
            'read_at' => now()
        ]);
    }

    /**
     * Find an inquiry owned by a user through the warranty relationship.
     */
    public function findOwnedByUser(int $inquiryId, int $userId): ?WarrantyInquiries
    {
        return WarrantyInquiries::with('warranty')
            ->where('id', $inquiryId)
            ->whereHas('warranty', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    }

    /**
     * Update inquiry status.
     */
    public function updateStatus(int $inquiryId, InquiryStatusType $status): void
    {
        WarrantyInquiries::where('id', $inquiryId)
            ->update([
                'status' => $status,
            ]);
    }

    /**
     * Mark an inquiry as unread.
     */
    public function markUnreadForInquiry(int $inquiryId): void
    {
        $inquiry = WarrantyInquiries::find($inquiryId);

        if ($inquiry) {
            $inquiry->update([
                'read_at' => null,
            ]);
        }
    }

    public function create(array $data): WarrantyInquiries
    {
        return WarrantyInquiries::create($data);
    }

    public function find(string $id): WarrantyInquiries
    {
        return WarrantyInquiries::findOrFail($id);
    }

    public function update(WarrantyInquiries $inquiry, array $data): WarrantyInquiries
    {
        $inquiry->update($data);

        return $inquiry;
    }
}
