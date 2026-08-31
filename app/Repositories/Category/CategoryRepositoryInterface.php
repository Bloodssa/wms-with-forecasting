<?php

namespace App\Repositories\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface CategoryRepositoryInterface
{
    public function create(array $attributes): Category;

    public function update(Category $category, array $attributes): Category;

    public function delete(Category $category): bool;

    /**
     * Get categories with product counts and seach based on name of category
     */
    public function getCategoryForProductDashboard(?string $search): Collection;

    public function getForFilter(): Collection;

    public function getForForm(): SupportCollection;
}
