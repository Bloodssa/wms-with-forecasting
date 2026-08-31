<?php

namespace App\Services;

use App\Enum\InquiryStatusType;
use App\Formatter\InquiryMessageFormatter;
use App\Repositories\Inquiry\InquiryRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductReview\ProductReviewRepositoryInterface;
use App\Repositories\Warranty\WarrantyRepositoryInterface;
use Illuminate\Support\Str;

class CustomerService
{
    public function __construct(
        private readonly WarrantyRepositoryInterface $warrantyRepository,
        private readonly InquiryRepositoryInterface $inquiryRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly ProductReviewRepositoryInterface $reviewRepository,
        protected InquiryMessageFormatter $messageFormatter
    ) {}

    /**
     * for the uath user home displaying with 10 products with ratings
     */
    public function getHomeData(int $userId): array
    {
        return [
            'stats' => [
                'activeWarranties' => $this->warrantyRepository->countActiveForUser($userId),
                'expWarCount' => $this->warrantyRepository->countNearExpiryForUser($userId),
                'resolvedInquiryCount' => $this->inquiryRepository->countResolvedForUser($userId),
            ],
            'recentlyPurchased' => $this->warrantyRepository->getRecentlyPurchasedForUser($userId, 3),
            'expiringWarranties' => $this->warrantyRepository->getExpiringOrExpiredForUser($userId, 3),
            'products' => $this->productRepository->getTopWithRatings(10),
        ];
    }

    public function getWarrantyListData(int $userId, array $filters): array
    {
        return [
            'warranties' => $this->warrantyRepository->paginateForCustomer($userId, $filters),
            'filters' => $filters,
        ];
    }

    public function getInquiriesData(int $userId, array $filters): array
    {
        return [
            'inquiries' => $this->inquiryRepository->paginateForCustomer($userId, $filters),
            'select' => InquiryStatusType::activeOptions(),
            'filters' => $filters,
            'warranties' => $this->warrantyRepository->getAllWithProductForUser($userId),
        ];
    }

    public function getHistoryData(int $userId): array
    {
        // registered warranty history map it with the type, date, title, and description
        $registeredWarranty = $this->warrantyRepository->getAllForUser($userId)
            ->map(fn($registered) => (object)[
                'type' => 'success',
                'date' => $registered->created_at,
                'title' => "Registered {$registered->product->name}",
                'description' => "Serial: {$registered->serial_number}",
                'url' => route('warranty.show', $registered->id)
            ]);

        $inquiries = $this->inquiryRepository->getAllWithWarrantyProductForUser($userId)
            ->map(fn($inquiry) => (object)[
                'type' => 'new',
                'date' => $inquiry->created_at,
                'title' => "Opened an inquiry for {$inquiry->warranty->product->name}",
                'description' => Str::limit($inquiry->message, 60),
                'url' => route('inquiry.show', $inquiry->id)
            ]);

        $statusUpdates = $this->inquiryRepository->getResolvedLikeWithWarrantyProductForUser($userId)
            ->map(function ($update) {

                $type = match ($update->status->value) {
                    'resolved' => 'success',
                    'replaced' => 'success',
                    'closed' => 'default',
                };

                return (object)[
                    'type' => $type,
                    'date' => $update->updated_at,
                    'title' => "Inquiry {$update->status->value}",
                    'description' => "Your inquiry for {$update->warranty->product->name} was {$update->status->value}.",
                    'url' => route('inquiry.show', $update->id)
                ];
            });

        $expiredWarranty = $this->warrantyRepository->getExpiredForUser($userId)
            ->map(fn($w) => (object)[
                'type' => 'expire',
                'date' => $w->expiry_date,
                'title' => "{$w->product->name} warranty expired",
                'description' => "Expired on " . $w->expiry_date->format('M d, Y'),
                'url' => route('warranty.show', $w->id)
            ]);

        $productReviews = $this->reviewRepository->getForUser($userId)
            ->map(fn($review) => (object)[
                'type' => 'success',
                'date' => $review->created_at,
                'title' => "Reviewed {$review->product->name}",
                'description' => "You gave {$review->rating} stars: " . Str::limit($review->comment, 60),
                'url' => route('product-reviews', $review->product_id)
            ]);

        // refactor with paginator
        // merge the queries to loop it in the blade foreach
        $history = collect()
            ->concat($registeredWarranty)
            ->concat($inquiries)
            ->concat($statusUpdates)
            ->concat($expiredWarranty)
            ->concat($productReviews)
            ->sortByDesc('date')
            ->values();

        return [
            'histories' => $history,
        ];
    }

    public function getWarrantyShowData(int $userId, string $id): array
    {
        $warranty = $this->warrantyRepository->findForUser($id, $userId);

        $history = $this->inquiryRepository->getByWarrantyId($warranty->id);

        // use the message formatter for the combined inquiry/response timeline
        $messages = $this->messageFormatter->format($warranty->inquiries);

        $latestInquiry = $this->inquiryRepository->findLatestForWarranty($warranty->id, $userId);

        $activeInquiry = $this->inquiryRepository->findActiveForWarrantyExcluding(
            $warranty->id,
            $userId,
            [InquiryStatusType::RESOLVED, InquiryStatusType::CLOSED, InquiryStatusType::REPLACED]
        );

        $review = $this->reviewRepository->findForUserAndProduct($userId, $warranty->product_id);

        return [
            'warranty' => $warranty,
            'history' => $history,
            'messages' => $messages,
            'latestInquiry' => $latestInquiry,
            'activeInquiry' => $activeInquiry,
            'review' => $review,
            'isExpired' => now()->greaterThan($warranty->expiry_date),
        ];
    }

    public function getInquiryShowData(int $userId, string $id, string $tab): array
    {
        $inquiry = $this->inquiryRepository->findWithRelationsForCustomer($id, $userId);

        $messages = $this->messageFormatter->format(collect([$inquiry]));

        $status = $inquiry->status instanceof InquiryStatusType
            ? $inquiry->status
            : InquiryStatusType::from($inquiry->status);

        $inquiry->is_done = $status->isFinal();

        return [
            'inquiry' => $inquiry,
            'messages' => $messages,
            'activeTab' => $tab,
        ];
    }
}
