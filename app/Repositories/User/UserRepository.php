<?php

namespace App\Repositories\User;

use App\Enum\UserRole;
use App\Enum\WarrantyStatusType;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function countCustomers(): int
    {
        return User::where('role', UserRole::CUSTOMER)->count();
    }

    public function create(array $attributes): User
    {
        return User::create($attributes);
    }

    public function findByGoogleIdOrEmail(string $googleId, string $email): ?User
    {
        return User::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();
    }

    public function paginateCustomers(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;

        return User::query()
            ->select(['name', 'email'])
            ->where('role', '=', 'customer')->withCount([
                'warranties as active_warranties_count' => function ($query) {
                    $query->whereIn('status', [WarrantyStatusType::ACTIVE, WarrantyStatusType::NEAR_EXPIRY]);
                },
                'warranties as expired_warranties_count' => function ($query) {
                    $query->where('status', WarrantyStatusType::EXPIRED);
                }
            ])
            ->withMax('warranties as last_inquiry_status', 'status') // max or latest created_at
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->paginate(10);
    }

    public function getStaffAndTechnicians(array $filters): Collection
    {
        $search = $filters['search'] ?? null;

        return User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->whereIn('role', [UserRole::STAFF, UserRole::TECHNICIAN])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->latest()
            ->get();
    }

    public function createFromGoogle(string $name, string $email, string $googleId): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'password' => null,
            'role' => UserRole::CUSTOMER->value
        ]);
    }

    /**
     * Attch user google id to existing user
     */
    public function linkGoogleAccount(User $user, string $googleId): User
    {
        $user->update([
            'google_id' => $googleId
        ]);

        return $user->refresh();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function updateRole(User $user, string $role): User
    {
        $user->update(['role' => $role]);

        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
