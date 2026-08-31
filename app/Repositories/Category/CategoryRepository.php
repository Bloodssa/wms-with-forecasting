<?php

namespace App\Repositories\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class CategoryRepository implements CategoryRepositoryInterface
{ 
    public function create(array $attributes): Category
    {
        return Category::create($attributes);
    }

    public function update(Category $category, array $attributes): Category
    {
        $category->update($attributes);

        return $category->refresh();
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function getCategoryForProductDashboard(?string $search): Collection
    {
        return Category::query()
            ->withCount('product')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('product_count', 'desc')
            ->get();
    }   

    public function getForFilter(): Collection
    {
        return Category::query()
            ->select(['name', 'slug'])
            ->get();
    }

    public function getForForm(): SupportCollection
    {
        return Category::query()
            ->pluck('name', 'id');
    }
}
