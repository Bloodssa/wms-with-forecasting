<?php

namespace App\Policies;

use App\Models\User;
use App\Enum\UserRole;

class CategoryPolicy
{
    public function create(User $user): bool
    {
        return $this->roleValidation($user);
    }

    public function update(User $user)
    {
        return $this->roleValidation($user);
    }

    private function roleValidation(User $user): bool
    {
        return in_array($user->role, [
            UserRole::ADMIN,
            UserRole::STAFF,
        ]);
    }
}
