<?php

namespace App\Policies;

use App\Enum\InquiryStatusType;
use App\Enum\UserRole;
use App\Models\User;
use App\Models\WarrantyInquiries;
use App\Models\Warranty;
use Illuminate\Auth\Access\Response;

class WarrantyInquiryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isTechnician() || $user->isStaff();
    }

    // any user can view
    public function view(User $user, WarrantyInquiries $inquiry): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WarrantyInquiries $inquiry): bool
    {
        if ($user->isTechnician()) {
            return true;
        }

        // allow user to update but only close status
        if ($user->role === 'customer' && $user->id === $inquiry->user_id) {
            return $inquiry->status !== InquiryStatusType::CLOSED;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->role === UserRole::ADMIN;
    }
}
