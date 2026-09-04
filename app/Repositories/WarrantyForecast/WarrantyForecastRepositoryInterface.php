<?php

namespace App\Repositories\WarrantyForecast;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface WarrantyForecastRepositoryInterface
{
    /**
     * product ids that have at least one warranty inquiry on record,
     */
    public function getProductIdsWithClaimHistory(): Collection;

    /**
     * total historical inquiry claim count for a product, all time.
     */
    public function getTotalClaimCountForProduct(int $productId): int;

    /**
     * inquiry count for a product within a date window.
     */
    public function getClaimCountForProduct(int $productId, Carbon $from, ?Carbon $to = null): int;

    public function getEarliestClaimDateForProduct(int $productId): ?Carbon;

    public function getActiveWarrantyCountForProduct(int $productId): int;

    public function getNearExpiryWarrantyCountForProduct(int $productId): int;

    /**
     * all recorded that the total_cost values for a product's service records,
     */
    public function getRepairCostsForProduct(int $productId, ?Carbon $since = null): Collection;

    /**
     * fallback average across every product's recorded service costs, used
     * only to flag no product-specific data
     */
    public function getGlobalAverageRepairCost(): ?float;

    public function getGlobalRepairCostSampleCount(): int;
}
