<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly ProductReviewService $productReviewService
    ) {}

    /**
     * Product dashboard and category data
     */
    public function getProductDashboardData(array $filter): array
    {
        return [
            'tab' => $filter['tab'] ?? 'products',
            'products' => $this->productRepository->getProducts(
                $filter['search'] ?? null,
                $filter['category'] ?? null
            ),
            'categories' => $this->categoryRepository->getCategoryForProductDashboard(
                $filter['category_search'] ?? null
            ),
            'categoriesForForm' => $this->categoryRepository->getForForm(),
            'categoriesForFilter' => $this->categoryRepository->getForFilter()
        ];
    }

    public function getProductDetailsAndReviews(Product $product): array
    {
        $product = $this->productRepository->getProductsAndReviews($product);

        return [
            'product' => $product,
            'ratingStats' => $this->productReviewService->getRatingStats($product)
        ];
    }

    public function create(array $data): Product
    {
        // if there is a set image save in public storage
        if (isset($data['product_image_url'])) {
            $data['product_image_url'] = $this->storeImage($data['product_image_url']);
        }

        return $this->productRepository->create($data);
    }

    public function update(Product $product, array $data)
    {
        // delete the image of a product if there is a new uploaded one
        if (isset($data['product_image_url'])) {
            $this->deleteImage($data['product_image_url']);

            $data['product_image_url'] = $this->storeImage(
                $data['product_image_url']
            );
        } else {
            unset($data['product_image_url']); // remove image url in the data
        }

        return $this->productRepository->update($product, $data);
    }

    public function delete(Product $product): bool
    {
        if ($product->warranties()->exists()) {
            return false;
        }

        $this->deleteImage($product->product_image_url);

        return $this->productRepository->delete($product);
    }

    /**
     * Get products show when users auth page
     */
    public function getCustomerProductsData(?string $search = null, ?string $category = null): array
    {
        return [
            'products' => $this->productRepository->getCustomerProducts($search, $category),
            'categories' => $this->categoryRepository->getForFilter(),
            'filters' => [
                'search' => $search,
                'category' => $category
            ]
        ];
    }

    /**
     * Product details in auth page of the user
     */
    public function getCustomerProductDetails(Product $product): array
    {
        $product = $this->productRepository->getProductsAndReviews($product);

        return [
            'product' => $product,
            'ratingStats' => $this->productReviewService->getRatingStats($product),
            'relatedProducts' => $this->productRepository->getRelatedProducts($product)
        ];
    }

    /**
     * Guest product page
     */
    public function getGuestProductsData(?string $search = null, ?string $category = null): array
    {
        return [
            'products' => $this->productRepository->getGuestProducts($search, $category),
            'categories' => $this->categoryRepository->getForFilter(),
            'filters' => [
                'search' => $search,
                'category' => $category,
            ]
        ];
    }

    /**
     * get product details for guest landing page.
     */
    public function getGuestProductDetails(Product $product): array
    {
        $product = $this->productRepository->getGuestProductDetails($product);

        return [
            'product' => $product,
            'ratingStats' => $this->productReviewService->getRatingStats($product),
        ];
    }

    /**
     * Helpers
     */

    private function storeImage(UploadedFile $file): string
    {
        return $file->store('products', 'public');
    }

    private function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }
}
