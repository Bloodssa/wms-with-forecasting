<?php

namespace App\Models;

use App\Enum\WarrantyStatusType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warranty extends Model
{
    protected $fillable = [
        'user_id',
        'product_id', 
        'claim_email',
        'serial_number', 
        'purchase_date', 
        'purchase_price',
        'expiry_date', 
        'status',
        'is_claimed'
    ];

    // cast the date to Carbon
    protected $casts = [
        'purchase_date' => 'date',
        'expiry_date' => 'date',
        'status' => WarrantyStatusType::class,
        'is_claimed' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(WarrantyInquiries::class);
    }

    /**
     * helper for get active or pending stats
     */
    public function scopeIsActive(Builder $query): Builder
    {
        return $query->whereIn('status', [WarrantyStatusType::ACTIVE, WarrantyStatusType::PENDING]);
    }

    public function scopeIsNearExpiry(Builder $query): Builder
    {
        return $query->where('status', WarrantyStatusType::NEAR_EXPIRY);
    }

    public function scopeIsExpired(Builder $query): Builder
    {
        return $query->where('status', WarrantyStatusType::EXPIRED);
    }
}
