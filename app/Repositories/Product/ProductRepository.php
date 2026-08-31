<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function getProducts(?string $search = null, ?string $category = null): LengthAwarePaginator
    {
        return Product::with('category')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $slug) {
                $query->whereHas('category', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function mostReportedInPeriod(Carbon $since, int $limit = 8): Collection
    {
        return DB::table('products as p')
            ->leftJoin('warranties as w', 'w.product_id', '=', 'p.id')
            ->leftJoin('warranty_inquiries as wi', function ($join) use ($since) {
                $join->on('wi.warranty_id', '=', 'w.id')
                    ->where('wi.created_at', '>=', $since);
            })
            ->select('p.name', DB::raw('COUNT(wi.id) as inquiries_count'))
            ->groupBy('p.name')
            ->orderByDesc('inquiries_count')
            ->take($limit)
            ->get();
    }

    public function getAllWithCategoryForRegistration(): Collection
    {
        return Product::query()
            ->select(['id', 'category_id', 'price', 'name', 'brand', 'product_image_url'])
            ->with('category:id,name')
            ->get();
    }

    public function getProductsAndReviews(Product $product): Product
    {
        return $product->load([
            'category:id,name',
            'reviews.user'
        ]);
    }

    public function getCustomerProducts(?string $search, ?string $category): Collection
    {
        return Product::query()
            ->select(['id', 'name', 'category_id', 'price', 'product_image_url', 'warranty_duration', 'brand'])
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $slug) {
                $query->whereHas('category', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            })
            ->latest()
            ->get();
    }

    public function getRelatedProducts(Product $product): Collection
    {
        return Product::query()
            ->select(['id', 'name', 'category_id', 'price', 'product_image_url', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->limit(10)
            ->inRandomOrder()
            ->get();
    }

    public function getGuestProducts(?string $search, ?string $category): Collection
    {
        return Product::query()
            ->select(['id', 'name', 'category_id', 'price', 'product_image_url', 'warranty_duration', 'brand'])
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query, $slug)  {
                $query->whereHas('category', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            })
            ->latest()
            ->get();
    }

    public function getGuestProductDetails(Product $product): Product
    {
        return $product->load([
            'category:id,name',
            'reviews.user',
        ]);
    }

    public function getTopWithRatings(int $limit = 10): Collection
    {
        return Product::query()
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function mostReported(int $limit = 5): Collection
    {
        return DB::table('products as p')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->join('warranties as w', 'p.id', '=', 'w.product_id')
            ->join('warranty_inquiries as wi', 'w.id', '=', 'wi.warranty_id')
            ->selectRaw('p.name, p.product_image_url, c.name as category_name, COUNT(wi.id) as total_inquiries')
            ->groupBy('p.id', 'p.name', 'c.name', 'product_image_url')
            ->orderByDesc('total_inquiries')
            ->limit($limit)
            ->get();
    }

    public function create(array $attributes): Product
    {
        return Product::query()->create($attributes);
    }

    public function update(Product $product, array $attributes): Product
    {
        $product->update($attributes);

        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }
}
