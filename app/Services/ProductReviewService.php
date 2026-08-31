<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use App\Repositories\ProductReview\ProductReviewRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ProductReviewService
{
    public function __construct(private readonly ProductReviewRepositoryInterface $productReviewRepository) {}

    public function create(User $user, Product $product, array $data, array $attachments = []): ProductReview
    {
        $data['user_id'] = $user->id;
        $data['product_id'] = $product->id;
        $data['attachments'] = $this->storeAttachments($attachments);

        return $this->productReviewRepository->create($data);
    }

    public function update(ProductReview $review, array $data, array $attachments = []): ProductReview
    {
        // get the existing file or image of the review
        $existing = $review->attachments ?? [];

        // get the remove request of the user
        $removed = collect($data['removed_attachments'] ?? [])
            ->map(fn($file) => trim($file))
            ->toArray();


        $this->deleteAttachments($existing, $removed);

        $remaining = collect($existing)
            ->reject(fn($file) => in_array($file, $removed))
            ->values()
            ->toArray();

        $newFiles = $this->storeAttachments($attachments);

        // store the left and new created files into a collections
        $data['attachments'] = collect(
            array_merge($remaining, $newFiles)
        )
            ->unique()
            ->values()
            ->toArray();

        unset($data['removed_attachments']);

        $data['edit_at'] = now();

        return $this->productReviewRepository->update(
            $review,
            $data
        );
    }

    public function reply(ProductReview $review, string $reply): ProductReview
    {
        return $this->productReviewRepository->update(
            $review,
            ['staff_reply' => $reply]
        );
    }

    public function deleteReply(ProductReview $review): ProductReview
    {
        return $this->productReviewRepository->update(
            $review,
            ['staff_reply' => null]
        );
    }

    public function getProductReviewsData(Product $product, User $user)
    {
        $product = $this->productReviewRepository->getProductReviews($product);

        $userReview = $this->productReviewRepository->getUserReview($product, $user);

        $userHasWarranty = $this->productReviewRepository->userHasWarranty($product, $user);

        $sortedReviews = $product->reviews->sortByDesc(fn($review) => $review->user_id === $user->id)->values();

        return [
            'product' => $product,
            'reviews' => $sortedReviews,
            'myReview' => $userReview,
            'canRateProduct' => $userHasWarranty && ! $userReview,
            'ratingStats' => $this->getRatingStats($product),
        ];
    }

    /**
     * @param array $attachments pass in controller
     * 
     * Create a new arrays of paths of the images
     */
    private function storeAttachments(array $attachments): array
    {
        return array_map(fn($file) => $file->store('reviews', 'public'), $attachments);
    }

    /**
     * @param array $existing images of the review
     * @param array $remove the image path to be delete
     */
    private function deleteAttachments(array $existing, array $removed): void
    {
        foreach ($existing as $file) {
            if (in_array($file, $removed)) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * @param Product
     * @return array total rating and average
     */
    public function getRatingStats(Product $product): array
    {
        $reviews = $product->reviews;

        $total = $reviews->count();

        if ($total === 0) {
            return [
                'average' => 0,
                'total' => 0,
            ];
        }

        return [
            'average' => round($reviews->avg('rating'), 1),
            'total' => $total,
        ];
    }
}
