<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\User;

class BasePolicy
{
    /**
     * Grant the admin all access
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === UserRole::ADMIN) {
            return true;
        }

        return null;
    }
}
