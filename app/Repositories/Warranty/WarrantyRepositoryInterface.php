<?php

namespace App\Repositories\Warranty;

use App\Models\User;
use App\Models\Warranty;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

interface WarrantyRepositoryInterface
{
    /**
     * get the summary count of the warranty active, near-expiry, and expired 
     * count per month and will be displayed as total range of 12 months
     */
    public function getChartRawData(Carbon $since): Collection;

    /**
     * list of all active warranties of customer with search filter
     */
    public function paginateForManager(array $filters): LengthAwarePaginator;

    public function findWithUserAndProduct(string $id): ?Warranty;

    /**
     * get warranties with near expiry and its owner
     */
    public function getNearExpiryWithUserAndProduct(): Collection;

    /**
     * get the unclaimed warranty of the user based on claim email
     */
    public function findUnclaimedIdsByEmail(string $email): array;

    public function claimForUser(User $user, array $warrantyIds): int;

    public function countActiveForUser(int $userId): int;

    /**
     * count the near expiry warranties
     */
    public function countNearExpiryForUser(int $userId): int;

    /**
     * get the first 3 recently purchase of the customer
     */
    public function getRecentlyPurchasedForUser(int $userId, int $limit = 3): Collection;

    /**
     * get the product details of near expiry or expired warranty of customer
     */
    public function getExpiringOrExpiredForUser(int $userId, int $limit = 3): Collection;

    /**
     * Get all the warranties of the users with search filter of the status
     */
    public function paginateForCustomer(int $userId, array $filters): LengthAwarePaginator;

    public function getAllWithProductForUser(int $userId): Collection;

    public function getAllForUser(int $userId): Collection;

    public function getExpiredForUser(int $userId): Collection;

    public function findForUser(string $id, int $userId): ?Warranty;

    public function findWithProductCategoryForUser(string $id, int $userId): ?Warranty;

    public function getUnclaimedByEmail(string $email): Collection;

    public function findPendingBySerialAndEmail(string $serialNumber, string $email): ?Warranty;

    public function countActiveGlobal(): int;

    public function claim(Warranty $warranty, int $userId): Warranty;

    public function getWithProductByIds(array $ids): Collection;

    public function update(Warranty $warranty, array $data): Warranty;

    public function delete(Warranty $warranty): bool;

    public function archive(Warranty $warranty): Warranty;

    public function unarchive(Warranty $warranty, string $computedStatus): Warranty;

    public function create(array $data): Warranty;

    public function find(int $warrantyId): Warranty;
}
