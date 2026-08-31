<?php

namespace App\Repositories\ProductReview;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Collection;

class ProductReviewRepository implements ProductReviewRepositoryInterface
{
    public function create(array $attributes): ProductReview
    {
        return ProductReview::query()->create($attributes);
    }

    public function update(ProductReview $productReview, array $attributes): ProductReview
    {
        $productReview->update($attributes);

        return $productReview->refresh();
    }

    public function delete(ProductReview $productReview): bool
    {
        return (bool) $productReview->delete();
    }


    public function getProductReviews(Product $product): Product
    {
        return $product->load([
            'reviews.user'
        ]);
    }

    public function getUserReview(Product $product, User $user): ?ProductReview
    {
        return $product->reviews()
            ->where('product_id', $product->id)
            ->first();
    }

    public function userHasWarranty(Product $product, User $user): bool
    {
        return $user->warranties()
            ->where('product_id', $product->id)
            ->exists();
    }

    public function getForUser(int $userId): Collection
    {
        return ProductReview::with('product')
            ->whereUserId($userId)
            ->get();
    }

    public function findForUserAndProduct(int $userId, int $productId): ?ProductReview
    {
        return ProductReview::whereUserId($userId)
            ->where('product_id', $productId)
            ->first();
    }
}
