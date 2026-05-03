<?php

namespace App\Http\Controllers;

use App\Enum\UserRole;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $tab = $request->tab ?? 'products';

        $products = Product::with('category')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($request->category, function ($query, $slug) {
                $query->whereHas('category', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->withCount('product')
            ->when($request->category_search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        $categoriesForFilter = Category::select('name', 'slug')->get();
        $categoriesForForm = Category::pluck('name', 'id');

        return Inertia::render('Manager/Products', [
            'tab' => $tab,
            'products' => $products,
            'categories' => $categories,
            'categoriesForForm' => $categoriesForForm,
            'categoriesForFilter' => $categoriesForFilter
        ]);
    }

    /**
     * Show admin product details
     */
    public function showProduct(string $id)
    {
        $product = Product::with(['category:id,name', 'reviews.user'])->findOrFail($id);

        return Inertia::render('Manager/ShowProduct', [
            'product' => $product,
            'ratingStats' => $this->ratignStats($product),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        // use policy if it can make product
        $this->authorize('create', Product::class);
        // validate with the StoreProductRequestClass
        $data = $request->validated();

        // store in public storage
        $imagePath = $request->file('product_image_url')->store('products', 'public');
        $data['product_image_url'] = $imagePath; 

        Product::create($data);

        return back()->with('success', 'Product Created Successfullly');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, int $id)
    {
        $product = Product::findOrFail($id);

        $this->authorize('delete', $product);

        $data = $request->validated();

        if ($request->hasFile('product_image_url')) {
            if ($product->product_image_url && Storage::disk('public')->exists($product->product_image_url)) {
                Storage::disk('public')->delete($product->product_image_url);
            }
            $imagePath = $request->file('product_image_url')->store('products', 'public');
            $data['product_image_url'] = $imagePath;
        } else {
            unset($data['product_image_url']);
        }

        $product->update($data);

        return back()->with('success', "Product {$product->name} edited successfully");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $product = Product::withCount('warranties')->findOrFail($id);

        $this->authorize('delete', $product);

        if ($product->warranties_count > 0) {
            return back()->with('error', "Cannot delete {$product->name}. It has active warranty records.");
        }

        // delete image if exists
        if ($product->product_image_url && Storage::disk('public')->exists($product->product_image_url)) {
            Storage::disk('public')->delete($product->product_image_url);
        }

        $productName = $product->name;

        $product->delete();

        return back()->with('success', "Product {$productName} deleted successfully");
    }

    /**
     * Store new category
     */
    public function storeCategory(Request $request)
    {
        $this->authorize('create', Product::class);
        
        $data = $request->validate([
            'name' => ['required', 'string']
        ]);

        Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);

        return back()->with('success', 'Created Successfully');
    }

    /**
     * Edit category
     */
    public function updateCategory(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,' . $id]
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);

        return back()->with('success', 'Category updated successfully');
    }

    /**
     * Destroy Category
     */
    public function destroyCategory(string $id)
    {
        $category = Category::findOrFail($id);

        // cant delete product if there is existing containing category
        if ($category->product()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing products.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully');
    }

    /**
     * Staff reply to review
     */
    public function reviewReply(Request $request, ProductReview $review)
    {
        // dd($request);
        if (!in_array(Auth::user()->role, [UserRole::ADMIN, UserRole::STAFF])) {
            abort(403);
        }

        $data = $request->validate([
            'staff_reply' => ['required', 'string', 'max:2000'],
        ]);

        $review->update([
            'staff_reply' => $data['staff_reply'],
        ]);

        return back()->with('success', 'Reply saved successfully');
    }

    /**
     * Delete staff review reply
     */
    public function deleteReply(ProductReview $review)
    {
        if (!in_array(Auth::user()->role, [UserRole::ADMIN, UserRole::STAFF])) {
            abort(403);
        }

        $review->update([
            'staff_reply' => null,
        ]);

        return back()->with('success', 'Reply deleted successfully');
    }

    /**
     * Customer Side
     */
    public function products(Request $request): Response
    {
        $products = Product::query()
            ->select(['id', 'name', 'category_id', 'price', 'product_image_url', 'warranty_duration', 'brand'])
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            })
            ->latest()
            ->get();

        return Inertia::render('Customer/Products', [
            'products' => $products,
            'categories' => Category::select('name', 'slug')->get(),
            'filters' => [
                'search' => request('search'),
                'category' => request('category'),
            ],
        ]);
    }

    public function productsDetails(string $id): Response
    {
        $product = Product::with(['category:id,name', 'reviews.user'])
            ->findOrFail($id);

        $relatedProducts = Product::query()
            ->select(['id', 'name', 'category_id', 'price', 'product_image_url', 'brand'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category:id,name'])
            ->withAvg('reviews as averageRating', 'rating')
            ->withCount('reviews')
            ->limit(10)
            ->inRandomOrder()
            ->get();

        return Inertia::render('Customer/ProductDetails', [
            'product' => $product,
            'ratingStats' => $this->ratignStats($product),
            'relatedProducts' => $relatedProducts
        ]);
    }

    public function productReviews(string $id): Response
    {
        $product = Product::query()->select(['id', 'name'])->with(['reviews.user']) ->findOrFail($id);

        $user = Auth::user();

        // get the review of the customer
        $myReview = $product->reviews->firstWhere('user_id', $user->id);

        // check if the customer has this product
        $hasWarranty = $user->warranties()->where('product_id', $product->id)->exists();

        // reorder put the auth user first in the list
        $sortedReviews = $product->reviews->sortByDesc(fn($review) => $review->user_id === $user->id)->values();

        return Inertia::render('Customer/Review', [
            'product' => $product,
            'reviews' => $sortedReviews,
            'myReview' => $myReview,
            'canRateProduct' => $hasWarranty && !$myReview,
            'ratingStats' => $this->ratignStats($product)
        ]);
    }

    /**
     * Store Customer review
     */
    public function storeReview(Request $request, string $id)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        $product = Product::findOrFail($id);

        $paths = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('reviews', 'public');
            }
        }

        ProductReview::create([
            'user_id' => Auth::user()->id,
            'product_id' => $product->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'attachments' => $paths
        ]);

        return back()->with('success', 'Review Success');
    }

    /**
     * Update the review of the customer
     */
    public function updateReview(Request $request, ProductReview $review)
    {
        // dd($review);
        // dd($request->removed_attachments);
        abort_unless($review->user_id === Auth::user()->id, 403); // continue if the auth user is same user_id

        $existing = $review->attachments ?? []; // get the attachments

        $removed = collect($request->removed_attachments ?? [])
            ->map(fn($file) => trim($file))
            ->toArray();

        // delete from storage
        foreach ($existing as $file) {
            if (in_array($file, $removed)) {
                Storage::disk('public')->delete($file);
            }
        }

        // remove the deleted images based on the passed removed_attach comes from the form submit
        $remaining = collect($existing)
            ->reject(fn($file) => in_array($file, $removed))
            ->values()
            ->toArray();

        $newFiles = [];

        if ($request->hasFile('attachments')) { // store the new file and store the path
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('reviews', 'public');
                $newFiles[] = $path;
            }
        }

        // merge all the path in the attachments
        $allFiles = collect(array_merge($remaining, $newFiles))
            ->unique()
            ->values()
            ->toArray();

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'attachments' => $allFiles,
            'edit_at' => now()
        ]);

        return back()->with('success', 'Review edited successfully');
    }


    /**
     * Helpers
     */

    /**
     * @param Product
     * @return array total rating and average
     */
    private function ratignStats(Product $product): array
    {
        $reviews = $product->reviews; // load reviews
        $total = $reviews->count(); // count

        if ($total === 0) {
            return [
                'average' => 0,
                'total' => 0,
            ];
        }

        $average = $reviews->avg('rating'); // get average rating with the loaded relation

        return [
            'average' => round($average, 1),
            'total' => $total,
        ];
    }
}
