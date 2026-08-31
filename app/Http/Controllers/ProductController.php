<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Http\Requests\Product\ProductDashboardRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Route;

class ProductController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ProductService $productService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(ProductDashboardRequest $request): Response
    {
        return Inertia::render('Manager/Products', $this->productService->getProductDashboardData($request->validated()));
    }

    /**
     * Show admin product details
     */
    public function show(Product $product)
    {
        return Inertia::render('Manager/ShowProduct', $this->productService->getProductDetailsAndReviews($product));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return back()->with('success', 'Product Created Successfullly');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->update($product, $request->validated());

        return back()->with('success', "Product {$product->name} edited successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $deleted = $this->productService->delete($product);

        return back()->with(
            $deleted ? 'success' : 'error',
            $deleted ? "Product {$product->name} deleted successfully"
                : "Cannot delete {$product->name}. It has active warranty records."
        );
    }

    /**
     * Customer Side
     */
    public function products(Request $request): Response
    {
        return Inertia::render('Customer/Products', $this->productService->getCustomerProductsData($request->search, $request->category));
    }

    public function productsDetails(Product $product): Response
    {
        return Inertia::render('Customer/ProductDetails', $this->productService->getCustomerProductDetails($product));
    }

    /**
     * Guest products
     */
    public function landingPageProducts(Request $request): Response
    {
        return Inertia::render('Customer/LandingProducts', [
            ... $this->productService->getGuestProductsData($request->input('search'), $request->input('category')),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    public function landingPageProductsDetails(Product $product)
    {
        return Inertia::render('Customer/LandingDetails', [
            ... $this->productService->getGuestProductDetails($product),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }
}
