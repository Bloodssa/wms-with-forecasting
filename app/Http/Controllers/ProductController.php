<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {   
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
            ->paginate(10);
    
        $categories = Category::query()
            ->when($request->category_search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10, ['*'], 'categories_page');

        $categoriesForFilter = Category::pluck('name', 'slug');
        $categoriesForForm = Category::pluck('name', 'id');

        return Inertia::render('Manager/Products', [
            'products' => $products,
            'categories' => $categories,
            'categoriesForForm' => $categoriesForForm,
            'categoriesForFilter' => $categoriesForFilter
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
