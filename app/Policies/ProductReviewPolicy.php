<?php

namespace App\Policies;

use App\Enum\UserRole;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;

class ProductReviewPolicy
{
    public function create( User $user, Product $product): bool 
    {
        // customer must have a warranty with this product
        $hasWarranty = $user->warranties()
            ->where('product_id', $product->id)
            ->exists();

        if (! $hasWarranty) {
            return false;
        }

        // customer can only review once
        return ! ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function update(User $user, ProductReview $review): bool
    {
        return $user->id === $review->user_id;
    }

    public function reply(User $user, ProductReview $review): bool
    {
        return in_array($user->role, [
            UserRole::ADMIN,
            UserRole::STAFF
        ]);
    }
}
