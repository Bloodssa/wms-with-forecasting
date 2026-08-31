<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get the products with category and seach for name, brand or category filter
     */
    public function getProducts(?string $search, ?string $categoty): LengthAwarePaginator;

    /**
     * get the product to be display in the registration form
     */
    public function getAllWithCategoryForRegistration(): Collection;

    /**
     * get the most reported products with the total count
     */
    public function mostReportedInPeriod(Carbon $since, int $limit = 8): Collection;

    /**
     * Get product details with the review of the verified customers
     */
    public function getProductsAndReviews(Product $product): Product;

    /**
     * Get the products for customer auth page
     */
    public function getCustomerProducts(?string $search, ?string $category): Collection;

    public function getRelatedProducts(Product $product): Collection;

    public function getGuestProducts(?string $search,?string $category): Collection;

    public function getGuestProductDetails(Product $product): Product;

    /**
     * get the most reported product and offset it base on the limit
     */
    public function mostReported(int $limit = 5): Collection;

    public function create(array $attributes): Product;

    public function update(Product $product, array $attributes): Product;

    public function delete(Product $product): bool;

    /**
     * get 10 products with reviews
     */
    public function getTopWithRatings(int $limit = 10): Collection;
}
