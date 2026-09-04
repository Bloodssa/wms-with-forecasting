<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\User;

class WarrantyServiceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::STAFF, UserRole::TECHNICIAN], true);
    }

    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::ADMIN, UserRole::STAFF, UserRole::TECHNICIAN], true);
    }
}
