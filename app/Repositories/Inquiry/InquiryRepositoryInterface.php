<?php

namespace App\Repositories\Inquiry;

use App\Enum\InquiryStatusType;
use App\Models\WarrantyInquiries;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface InquiryRepositoryInterface
{
    /**
     * latest warranty with user name and product name
     */
    public function getLatestWithWarrantyProduct(int $limit = 5): Collection;

    /**
     * warranty inquiry summary for dashboard
     */
    public function getLatestWithWarrantySummary(int $limit = 5): Collection;

    /**
     * list of all warranty inquiries
     */
    public function paginateForManager(array $filters): LengthAwarePaginator;

    public function findWithFullRelations(int $id): WarrantyInquiries;
    
    /**
     * count inquiry of every mount or day base on filter search
     */
    public function getInquiryCountsGroupedByPeriod(Carbon $since, string $format): Collection;

    /**
     * count warranty inquiries based on created_at
     */
    public function countSince(Carbon $since): int;

    public function countOpenGlobal(): int;

    public function countSinceWithStatus(Carbon $since, array $statuses): int;

    /**
     * count the resolved inquiry of the users
     */
    public function countResolvedForUser(int $userId): int;

    /**
     * get all the customer warranties except resolved,replaced, closed
     */
    public function paginateForCustomer(int $userId, array $filters): LengthAwarePaginator;

    public function getAllWithWarrantyProductForUser(int $userId): Collection;

    /**
     * get the resolved warranty inquiry of the user with url route path of the id
     */
    public function getResolvedLikeWithWarrantyProductForUser(int $userId): Collection;

    public function getByWarrantyId(string $warrantyId): Collection;

    public function findLatestForWarranty(string $warrantyId, int $userId): ?WarrantyInquiries;

    /**
     * get active warranty inquiries with excluding status
     */
    public function findActiveForWarrantyExcluding(string $warrantyId, int $userId, array $excludedStatuses): ?WarrantyInquiries;

    public function findWithRelationsForCustomer(string $id, int $userId): WarrantyInquiries;

    /**
     * check if user has existing warranty based on the @param array $finalStatuses
     */
    public function hasActiveForWarranty(string $warrantyId, array $finalStatuses): bool;

    public function markRead(string $id): void;

    public function findOwnedByUser(int $inquiryId, int $userId): ?WarrantyInquiries;

    public function updateStatus(int $inquiryId, InquiryStatusType $status): void;

    public function markUnreadForInquiry(int $inquiryId): void;
    
    public function create(array $data): WarrantyInquiries;

    public function find(string $id): WarrantyInquiries;

    public function update(WarrantyInquiries $inquiry, array $data): WarrantyInquiries;
}
