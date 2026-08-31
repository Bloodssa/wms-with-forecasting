<?php

namespace App\Repositories\Warranty;

use App\Enum\WarrantyStatusType;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class WarrantyRepository implements WarrantyRepositoryInterface
{
    public function getChartRawData(Carbon $since): Collection
    {
        return Warranty::selectRaw("
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(status IN ('active', 'pending')) as active,
            SUM(status = 'near-expiry') as near_expiry,
            SUM(status = 'expired') as expired
        ")
            ->where('created_at', '>=', $since)
            ->groupBy('month')
            ->get();
    }

    public function paginateForManager(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        return Warranty::query()
            ->select(['id', 'product_id', 'user_id', 'serial_number', 'archived_at', 'is_claimed', 'claim_email', 'status', 'purchase_date', 'expiry_date'])
            ->with(['product:id,name,product_image_url', 'user:id,name'])
            ->when($search, function ($query, $search) {
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
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10);
    }

    public function findWithUserAndProduct(string $id): ?Warranty
    {
        return Warranty::with(['user', 'product.category'])
            ->where('id', $id)
            ->firstOrFail();
    }
    
    public function getNearExpiryWithUserAndProduct(): Collection
    {
        return Warranty::with('user', 'product')
            ->where('status', WarrantyStatusType::NEAR_EXPIRY)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findUnclaimedIdsByEmail(string $email): array
    {
        return Warranty::query()
            ->where('claim_email', strtolower(trim($email)))
            ->where('is_claimed', false)
            ->pluck('id')
            ->toArray();
    }

    public function claimForUser(User $user, array $warrantyIds): int
    {
        return Warranty::query()
            ->whereIn('id', $warrantyIds)
            ->where('is_claimed', false)
            ->update([
                'user_id' => $user->id,
                'is_claimed' => true,
                'status' => WarrantyStatusType::ACTIVE->value,
                'updated_at'  => now(),
            ]);
    }

    public function countActiveForUser(int $userId): int
    {
        return Warranty::whereUserId($userId)
            ->where('status', '!=', WarrantyStatusType::EXPIRED->value)
            ->count();
    }

    public function countNearExpiryForUser(int $userId): int
    {
        return Warranty::whereUserId($userId)
            ->where('status', WarrantyStatusType::NEAR_EXPIRY->value)
            ->count();
    }

    public function getRecentlyPurchasedForUser(int $userId, int $limit = 3): Collection
    {
        return Warranty::query()
            ->whereUserId($userId)
            ->select(['id', 'purchase_date', 'product_id'])
            ->with('product:id,name,product_image_url')
            ->latest('purchase_date')
            ->take($limit)
            ->get();
    }

    public function getExpiringOrExpiredForUser(int $userId, int $limit = 3): Collection
    {
        return Warranty::query()
            ->select(['id', 'purchase_date', 'expiry_date', 'product_id'])
            ->whereUserId($userId)
            ->with('product:id,name,product_image_url')
            ->whereIn('status', [WarrantyStatusType::EXPIRED->value, WarrantyStatusType::NEAR_EXPIRY->value])
            ->latest('expiry_date')
            ->limit($limit)
            ->get();
    }

    public function paginateForCustomer(int $userId, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;

        return Warranty::query()
            ->select(['id', 'serial_number', 'status', 'expiry_date', 'purchase_date', 'product_id'])
            ->with(['product' => function ($query) {
                $query->select([
                    'id',
                    'name',
                    'product_image_url',
                    'category_id',
                    'brand'
                ]);
            }, 'product.category:id,name'])
            ->withCount([ // count unread messages that send by technician
                'inquiries as unread_message_count' => function ($q) {
                    $q->whereHas('responses', function ($q2) {
                        $q2->whereNull('read_at')
                            ->where('user_id', '!=', Auth::id());
                    });
                }
            ])
            ->whereUserId($userId)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('serial_number', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })->when($status, function ($query, $status) {
                if ($status === 'active') $query->where('expiry_date', '>', now()->addDays(30));
                if ($status === 'near-expiry') $query->whereBetween('expiry_date', [now(), now()->addDays(30)]);
                if ($status === 'expired') $query->where('expiry_date', '<', now());
            })
            ->paginate(10)
            ->withQueryString();
    }

    public function getAllWithProductForUser(int $userId): Collection
    {
        return Warranty::with(['product'])
            ->whereUserId($userId)
            ->get();
    }

    public function getAllForUser(int $userId): Collection
    {
        return Warranty::with('product')
            ->whereUserId($userId)
            ->get();
    }

    public function getExpiredForUser(int $userId): Collection
    {
        return Warranty::with('product')
            ->whereUserId($userId)
            ->whereDate('expiry_date', '<=', now())
            ->get();
    }

    public function findForUser(string $id, int $userId): ?Warranty
    {
        return Warranty::with(['product.category', 'inquiries', 'inquiries.responses.user'])
            ->whereUserId($userId)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function findWithProductCategoryForUser(string $id, int $userId): ?Warranty
    {
        return Warranty::with('product.category')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function getUnclaimedByEmail(string $email): Collection
    {
        return Warranty::with('product')
            ->where('claim_email', $email)
            ->where('is_claimed', false)
            ->get();
    }

    public function findPendingBySerialAndEmail(string $serialNumber, string $email): ?Warranty
    {
        return Warranty::where('serial_number', $serialNumber)
            ->where('claim_email', $email)
            ->whereNull('user_id')
            ->first();
    }

    public function countActiveGlobal(): int
    {
        return Warranty::isActive()->count();
    }

    public function claim(Warranty $warranty, int $userId): Warranty
    {
        $warranty->update([
            'user_id' => $userId,
            'status' => WarrantyStatusType::ACTIVE,
            'is_claimed' => true,
        ]);

        return $warranty;
    }

    public function getWithProductByIds(array $ids): Collection
    {
        return Warranty::with('product')->whereIn('id', $ids)->get();
    }

    public function update(Warranty $warranty, array $data): Warranty
    {
        $warranty->update($data);

        return $warranty;
    }

    public function delete(Warranty $warranty): bool
    {
        return $warranty->delete();
    }

    public function archive(Warranty $warranty): Warranty
    {
        $warranty->update([
            'status' => WarrantyStatusType::ARCHIVED,
            'archived_at' => now(),
        ]);

        return $warranty;
    }

    public function unarchive(Warranty $warranty, string $computedStatus): Warranty
    {
        $warranty->archived_at = null;
        $warranty->status = $computedStatus;

        $warranty->save();

        return $warranty->refresh();
    }

    public function create(array $data): Warranty
    {
        return Warranty::create($data);
    }
}
