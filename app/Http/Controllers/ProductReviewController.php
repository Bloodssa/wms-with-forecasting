<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductReview\ReplyProductReviewRequest;
use App\Http\Requests\ProductReview\StoreProductReviewRequest;
use App\Http\Requests\ProductReview\UpdateProductReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\ProductReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;

class ProductReviewController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly ProductReviewService $productReviewService) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        $this->productReviewService->create($request->user(), $product, $request->validated(), $request->file('attachments', []));

        return back()->with('success', 'Review Success');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductReviewRequest $request, ProductReview $review): RedirectResponse
    {
        $this->productReviewService->update($review, $request->validated(), $request->file('attachments', []));

        return back()->with('success', 'Review edited successfully');
    }

    /**
     * Admin or staff reply to the review of the user 1 response only
     */
    public function reply(ReplyProductReviewRequest $request, ProductReview $review): RedirectResponse
    {
        $this->productReviewService->reply($review, $request->validated()['staff_reply']);

        return back()->with('success', 'Reply saved successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductReview $review)
    {
        $this->authorize('reply', $review);

        $this->productReviewService->deleteReply($review);
    }

    public function productReviews(Product $product): Response
    {
        return Inertia::render('Customer/Review', $this->productReviewService->getProductReviewsData($product, request()->user()));
    }
}
