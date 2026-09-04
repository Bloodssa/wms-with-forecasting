<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\WarrantyServiceRecord;
use App\Services\WarrantyForecastService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class WarrantyForecastController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected WarrantyForecastService $forecastService) {}

    /**
     * Forecast dashboard: summary, ranked high-risk products, disclaimer.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', WarrantyServiceRecord::class);

        return Inertia::render('Manager/WarrantyForecast', $this->forecastService->getDashboardForecastData());
    }

    /**
     * Detailed forecast for a single product (risk breakdown, cost range, reasons).
     */
    public function show(Product $product): Response
    {
        $this->authorize('view', WarrantyServiceRecord::class);

        return Inertia::render('Manager/WarrantyForecastProduct', [
            'forecast' => $this->forecastService->forecastProduct($product),
            'disclaimer' => WarrantyForecastService::DISCLAIMER,
        ]);
    }
}
