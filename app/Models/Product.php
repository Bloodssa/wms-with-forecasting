<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'category_id', 'brand','price', 'description',
        'warranty_duration', 'product_image_url', 
        'service_center_name', 'service_center_address'
    ];

    // image path
    protected $appends = ['image_url'];

    /**
     * Image path getter
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->product_image_url);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function warranties(): HasMany
    {
        return $this->hasMany(Warranty::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
