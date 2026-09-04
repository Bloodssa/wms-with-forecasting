<?php

namespace App\Services\Forecasting;

final class ForecastCalculator
{
    /**
     * Minimum number of historical claims before a claim-rate figure is
     * considered meaningful at all.
     */
    public const MIN_CLAIMS_FOR_ANY_CONFIDENCE = 1;

    /**
     * Minimum number of actual cost samples before a cost forecast is offered.
     */
    public const MIN_COST_SAMPLES = 3;

    /**
     * Claims per month, bounded to a minimum 1-month observation window so a
     * brand-new product with one claim today doesn't produce a division
     * blow-up or an inflated rate.
     */
    public function claimRatePerMonth(int $totalClaims, int $monthsObserved): float
    {
        $months = max(1, $monthsObserved);

        return round($totalClaims / $months, 3);
    }

    /**
     * Compares two equal-length consecutive periods (e.g. "last 3 months"
     * vs "the 3 months before that") and returns a direction + percent change.
     *
     * @return array{direction: string, percentChange: float|null}
     */
    public function trend(int $recentCount, int $previousCount): array
    {
        if ($previousCount === 0) {
            // Can't compute a meaningful percentage change from a zero baseline.
            // Report the direction (any new claims at all = increasing) without
            // fabricating a percentage.
            return [
                'direction' => $recentCount > 0 ? 'increasing' : 'stable',
                'percentChange' => null,
            ];
        }

        $percentChange = round((($recentCount - $previousCount) / $previousCount) * 100, 1);

        $direction = match (true) {
            $percentChange > 5 => 'increasing',
            $percentChange < -5 => 'decreasing',
            default => 'stable',
        };

        return [
            'direction' => $direction,
            'percentChange' => $percentChange,
        ];
    }

    /**
     * Naive linear projection: continue the recent monthly claim rate forward.
     * This is deliberately simple (no seasonality, no ML) so it stays explainable.
     */
    public function predictClaims(float $ratePerMonth, int $projectionMonths): int
    {
        return (int) round($ratePerMonth * max(1, $projectionMonths));
    }

    /**
     * @param  array<int, float>  $costs  actual total_cost samples
     * @return array{available: bool, reason: string|null, average: float|null, min: float|null, max: float|null, sampleCount: int}
     */
    public function costStats(array $costs): array
    {
        $sampleCount = count($costs);

        if ($sampleCount === 0) {
            return [
                'available' => false,
                'reason' => 'insufficient_historical_cost_data',
                'average' => null,
                'min' => null,
                'max' => null,
                'sampleCount' => 0,
            ];
        }

        if ($sampleCount < self::MIN_COST_SAMPLES) {
            return [
                'available' => false,
                'reason' => 'insufficient_historical_cost_data',
                'average' => round(array_sum($costs) / $sampleCount, 2),
                'min' => round(min($costs), 2),
                'max' => round(max($costs), 2),
                'sampleCount' => $sampleCount,
            ];
        }

        return [
            'available' => true,
            'reason' => null,
            'average' => round(array_sum($costs) / $sampleCount, 2),
            'min' => round(min($costs), 2),
            'max' => round(max($costs), 2),
            'sampleCount' => $sampleCount,
        ];
    }

    /**
     * Expected cost = predicted future claims x average historical repair cost.
     * Returns null (not zero) when there isn't a usable average, so callers
     * don't mistake "no data" for "zero cost".
     */
    public function expectedCost(int $predictedClaims, ?float $averageCost): ?float
    {
        if ($averageCost === null) {
            return null;
        }

        return round($predictedClaims * $averageCost, 2);
    }

    /**
     * Sample-count-based confidence — never a fabricated statistical percentage.
     */
    public function confidenceFromSampleCount(int $sampleCount): string
    {
        return match (true) {
            $sampleCount === 0 => 'insufficient_data',
            $sampleCount < 3 => 'low',
            $sampleCount < 10 => 'medium',
            default => 'high',
        };
    }

    /**
     * Documented, explainable risk score (0-100).
     *
     * Weights:
     *  - recent claims (last 3 months): x10 each, capped contribution 50
     *  - historical claims (all-time):  x2 each, capped contribution 30
     *  - increasing trend:              +0 to 15, scaled from percent change
     *  - active warranties on the book: x1 each, capped contribution 15
     *  - near-expiry warranties:        x1.5 each, capped contribution 10
     *
     * Capped at 100 total. This is intentionally a simple additive rule-based
     * score, not a trained model — the goal is that a manager can look at the
     * inputs and understand exactly why a product scored the way it did.
     *
     * @param array{
     *     recentClaims: int,
     *     historicalClaims: int,
     *     trendPercentChange: float|null,
     *     activeWarranties: int,
     *     nearExpiryWarranties: int,
     * } $metrics
     */
    public function riskScore(array $metrics): int
    {
        $recentContribution = min(50, $metrics['recentClaims'] * 10);
        $historicalContribution = min(30, $metrics['historicalClaims'] * 2);

        $trendContribution = 0;
        if (($metrics['trendPercentChange'] ?? null) !== null && $metrics['trendPercentChange'] > 0) {
            $trendContribution = min(15, $metrics['trendPercentChange'] * 0.3);
        }

        $activeContribution = min(15, $metrics['activeWarranties'] * 1);
        $nearExpiryContribution = min(10, $metrics['nearExpiryWarranties'] * 1.5);

        $score = $recentContribution + $historicalContribution + $trendContribution
            + $activeContribution + $nearExpiryContribution;

        return (int) round(min(100, $score));
    }

    public function riskLevel(int $score): string
    {
        return match (true) {
            $score >= 70 => 'high',
            $score >= 40 => 'medium',
            $score > 0 => 'low',
            default => 'minimal',
        };
    }

    /**
     * Human-readable reasons behind a risk score, for UI display
     * (guide section 37 — explain the score, don't just show a number).
     *
     * @param array{
     *     recentClaims: int,
     *     historicalClaims: int,
     *     trendPercentChange: float|null,
     *     activeWarranties: int,
     *     averageRepairCost: float|null,
     * } $metrics
     * @return array<int, string>
     */
    public function riskReasons(array $metrics): array
    {
        $reasons = [];

        if ($metrics['recentClaims'] > 0) {
            $reasons[] = "{$metrics['recentClaims']} warranty claim" . ($metrics['recentClaims'] === 1 ? '' : 's') . " in the last 3 months";
        }

        if (($metrics['trendPercentChange'] ?? null) !== null && $metrics['trendPercentChange'] > 0) {
            $reasons[] = "Claim frequency increased by {$metrics['trendPercentChange']}%";
        }

        if ($metrics['activeWarranties'] > 0) {
            $reasons[] = "{$metrics['activeWarranties']} active warrant" . ($metrics['activeWarranties'] === 1 ? 'y' : 'ies');
        }

        if ($metrics['averageRepairCost'] !== null) {
            $reasons[] = 'Average repair cost: ₱' . number_format($metrics['averageRepairCost'], 2);
        }

        if (empty($reasons)) {
            $reasons[] = 'No significant warranty activity recorded yet';
        }

        return $reasons;
    }

    /**
     * parts + labor = total, used when auto-computing a missing total_cost.
     */
    public function sumCost(?float $partsCost, ?float $laborCost): float
    {
        return round(($partsCost ?? 0) + ($laborCost ?? 0), 2);
    }
}
