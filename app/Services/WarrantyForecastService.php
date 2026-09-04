<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Inquiry\InquiryRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Warranty\WarrantyRepositoryInterface;
use App\Repositories\WarrantyForecast\WarrantyForecastRepositoryInterface;
use App\Services\Forecasting\ForecastCalculator;
use Illuminate\Support\Collection;

class WarrantyForecastService
{
    /**
     * how many months back "recent" trend comparisons look, on each side
     */
    private const TREND_WINDOW_MONTHS = 3;

    /**
     * how many months forward claim/cost projections cover.
     */
    private const PROJECTION_MONTHS = 3;

    /**
     * how far back the dashboard's high-risk product shortlist is capped.
     */
    private const MAX_DASHBOARD_PRODUCTS = 10;

    public const DISCLAIMER = 'Forecasts are estimates based on historical warranty activity and recorded repair costs. Actual future costs may vary.';

    public function __construct(
        protected WarrantyForecastRepositoryInterface $forecastRepository,
        protected WarrantyRepositoryInterface $warrantyRepository,
        protected InquiryRepositoryInterface $inquiryRepository,
        protected ProductRepositoryInterface $productRepository,
        protected ForecastCalculator $calculator,
    ) {}

    /**
     * Dashboard-level forecast summary + a ranked shortlist of products.
     */
    public function getDashboardForecastData(): array
    {
        $productIds = $this->forecastRepository->getProductIdsWithClaimHistory();

        if ($productIds->isEmpty()) {
            return [
                'summary' => [
                    'activeWarranties' => $this->warrantyRepository->countActiveGlobal(),
                    'predictedClaims' => 0,
                    'estimatedRepairCost' => null,
                    'highRiskProducts' => 0,
                ],
                'products' => [],
                'disclaimer' => self::DISCLAIMER,
                'insufficientData' => true,
            ];
        }

        $products = $this->productRepository->findMany($productIds->all())->keyBy('id');

        $forecasts = $productIds
            ->map(fn ($productId) => $products->get($productId))
            ->filter()
            ->map(fn (Product $product) => $this->forecastProduct($product))
            ->sortByDesc('riskScore')
            ->values();

        $predictedClaimsTotal = (int) $forecasts->sum('predictedClaims');

        $estimatedCostTotal = $forecasts
            ->pluck('estimatedRepairCost')
            ->filter(fn ($cost) => $cost !== null)
            ->sum();

        $anyCostAvailable = $forecasts->contains(fn ($f) => $f['estimatedRepairCost'] !== null);

        return [
            'summary' => [
                'activeWarranties' => $this->warrantyRepository->countActiveGlobal(),
                'predictedClaims' => $predictedClaimsTotal,
                'estimatedRepairCost' => $anyCostAvailable ? round($estimatedCostTotal, 2) : null,
                'highRiskProducts' => $forecasts->where('riskLevel', 'high')->count(),
            ],
            'products' => $forecasts->take(self::MAX_DASHBOARD_PRODUCTS)->values()->all(),
            'disclaimer' => self::DISCLAIMER,
            'insufficientData' => false,
        ];
    }

    /**
     * Full forecast for a single product: risk score/level/reasons, claim
     * projection, and repair-cost projection (when enough cost data exists).
     */
    public function forecastProduct(Product $product): array
    {
        $now = now();
        $recentSince = $now->copy()->subMonths(self::TREND_WINDOW_MONTHS);
        $previousSince = $now->copy()->subMonths(self::TREND_WINDOW_MONTHS * 2);

        $historicalClaims = $this->forecastRepository->getTotalClaimCountForProduct($product->id);
        $recentClaims = $this->forecastRepository->getClaimCountForProduct($product->id, $recentSince);
        $previousClaims = $this->forecastRepository->getClaimCountForProduct($product->id, $previousSince, $recentSince);

        $earliestClaim = $this->forecastRepository->getEarliestClaimDateForProduct($product->id);
        $monthsObserved = $earliestClaim ? max(1, (int) ceil($earliestClaim->diffInDays($now) / 30)) : 1;

        $activeWarranties = $this->forecastRepository->getActiveWarrantyCountForProduct($product->id);
        $nearExpiryWarranties = $this->forecastRepository->getNearExpiryWarrantyCountForProduct($product->id);

        $trend = $this->calculator->trend($recentClaims, $previousClaims);

        $claimRate = $this->calculator->claimRatePerMonth($historicalClaims, $monthsObserved);
        $predictedClaims = $this->calculator->predictClaims($claimRate, self::PROJECTION_MONTHS);

        $costForecast = $this->forecastRepairCost($product);

        $riskMetrics = [
            'recentClaims' => $recentClaims,
            'historicalClaims' => $historicalClaims,
            'trendPercentChange' => $trend['percentChange'],
            'activeWarranties' => $activeWarranties,
            'nearExpiryWarranties' => $nearExpiryWarranties,
        ];

        $riskScore = $this->calculator->riskScore($riskMetrics);
        $riskLevel = $this->calculator->riskLevel($riskScore);

        $reasons = $this->calculator->riskReasons([
            'recentClaims' => $recentClaims,
            'historicalClaims' => $historicalClaims,
            'trendPercentChange' => $trend['percentChange'],
            'activeWarranties' => $activeWarranties,
            'averageRepairCost' => $costForecast['averageRepairCost'],
        ]);

        $estimatedRepairCost = $this->calculator->expectedCost($predictedClaims, $costForecast['averageRepairCost']);

        return [
            'product' => $product,
            'riskScore' => $riskScore,
            'riskLevel' => $riskLevel,
            'reasons' => $reasons,
            'historicalClaims' => $historicalClaims,
            'recentClaims' => $recentClaims,
            'trend' => $trend,
            'predictedClaims' => $predictedClaims,
            'averageRepairCost' => $costForecast['averageRepairCost'],
            'repairCostRange' => $costForecast['range'],
            'estimatedRepairCost' => $estimatedRepairCost,
            'confidence' => $this->calculator->confidenceFromSampleCount($historicalClaims),
            'costConfidence' => $costForecast['confidence'],
            'costDataAvailable' => $costForecast['available'],
            'activeWarranties' => $activeWarranties,
            'nearExpiryWarranties' => $nearExpiryWarranties,
        ];
    }

    /**
     * Repair-cost-only forecast for a product, isolated so it can be reused
     * (and tested) independently of the full risk forecast.
     *
     * @return array{
     *     available: bool,
     *     reason: string|null,
     *     averageRepairCost: float|null,
     *     range: array{min: float, max: float}|null,
     *     confidence: string,
     *     sampleCount: int,
     * }
     */
    public function forecastRepairCost(Product $product): array
    {
        $costs = $this->forecastRepository->getRepairCostsForProduct($product->id)->all();
        $stats = $this->calculator->costStats($costs);

        return [
            'available' => $stats['available'],
            'reason' => $stats['reason'],
            'averageRepairCost' => $stats['average'],
            'range' => $stats['available'] ? ['min' => $stats['min'], 'max' => $stats['max']] : null,
            'confidence' => $this->calculator->confidenceFromSampleCount($stats['sampleCount']),
            'sampleCount' => $stats['sampleCount'],
        ];
    }

    /**
     * Products currently classified "high" risk, most-recently-computed order.
     * Reuses forecastProduct() rather than re-implementing the risk logic.
     */
    public function getHighRiskProducts(): Collection
    {
        $productIds = $this->forecastRepository->getProductIdsWithClaimHistory();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = $this->productRepository->findMany($productIds->all());

        return $products
            ->map(fn (Product $product) => $this->forecastProduct($product))
            ->filter(fn (array $forecast) => $forecast['riskLevel'] === 'high')
            ->sortByDesc('riskScore')
            ->values();
    }
}
