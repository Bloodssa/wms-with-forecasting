<?php

namespace App\Repositories\WarrantyForecast;

use App\Models\WarrantyInquiries;
use App\Models\WarrantyServiceRecord;
use App\Enum\WarrantyStatusType;
use App\Models\Warranty;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class WarrantyForecastRepository implements WarrantyForecastRepositoryInterface
{
    public function getProductIdsWithClaimHistory(): Collection
    {
        return WarrantyInquiries::query()
            ->join('warranties', 'warranties.id', '=', 'warranty_inquiries.warranty_id')
            ->distinct()
            ->pluck('warranties.product_id');
    }

    public function getTotalClaimCountForProduct(int $productId): int
    {
        return WarrantyInquiries::query()
            ->whereHas('warranty', fn ($q) => $q->where('product_id', $productId))
            ->count();
    }

    public function getClaimCountForProduct(int $productId, Carbon $from, ?Carbon $to = null): int
    {
        return WarrantyInquiries::query()
            ->whereHas('warranty', fn ($q) => $q->where('product_id', $productId))
            ->where('created_at', '>=', $from)
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->count();
    }

    public function getEarliestClaimDateForProduct(int $productId): ?Carbon
    {
        $inquiry = WarrantyInquiries::query()
            ->whereHas('warranty', fn ($q) => $q->where('product_id', $productId))
            ->oldest()
            ->first(['created_at']);

        return $inquiry?->created_at;
    }

    public function getActiveWarrantyCountForProduct(int $productId): int
    {
        return Warranty::where('product_id', $productId)
            ->whereIn('status', [WarrantyStatusType::ACTIVE->value, WarrantyStatusType::NEAR_EXPIRY->value])
            ->count();
    }

    public function getNearExpiryWarrantyCountForProduct(int $productId): int
    {
        return Warranty::where('product_id', $productId)
            ->where('status', WarrantyStatusType::NEAR_EXPIRY->value)
            ->count();
    }

    public function getRepairCostsForProduct(int $productId, ?Carbon $since = null): Collection
    {
        return WarrantyServiceRecord::query()
            ->whereHas('inquiry.warranty', fn ($q) => $q->where('product_id', $productId))
            ->when($since, fn ($q) => $q->where('warranty_service_records.created_at', '>=', $since))
            ->pluck('total_cost')
            ->map(fn ($cost) => (float) $cost);
    }

    public function getGlobalAverageRepairCost(): ?float
    {
        $average = WarrantyServiceRecord::query()->avg('total_cost');

        return $average !== null ? (float) $average : null;
    }

    public function getGlobalRepairCostSampleCount(): int
    {
        return WarrantyServiceRecord::query()->count();
    }
}