<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Category\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private readonly CategoryRepositoryInterface $categoryRepository) {}

    public function create(array $data): Category
    {
        return $this->categoryRepository->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);
    }

    public function update(Category $category, array $data): Category
    {
        return $this->categoryRepository->update($category, [
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);
    }

    public function delete(Category $category): bool
    {
        // cant delete product if there is existing containing category
        if ($category->product()->exists())
            return false;

        return (bool) $this->categoryRepository->delete($category);
    }
}
