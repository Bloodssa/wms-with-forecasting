<?php

namespace App\Repositories\ProductReview;

use App\Models\ProductReview;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

interface ProductReviewRepositoryInterface
{
    public function create(array $attributes): ProductReview;

    public function update(ProductReview $productReview, array $attributes): ProductReview;

    public function delete(ProductReview $productReview): bool;
    
    public function getProductReviews(Product $product): Product;

    /**
     * get the auth user review
     */
    public function getUserReview(Product $product, User $user): ?ProductReview;

    /**
     * Check if user warranty exists
     */
    public function userHasWarranty(Product $product, User $user): bool;

    public function getForUser(int $userId): Collection;

    public function findForUserAndProduct(int $userId, int $productId): ?ProductReview;
}
