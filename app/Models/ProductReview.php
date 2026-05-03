<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id', 'user_id',
        'rating', 'comment', 'attachments',
        'staff_reply', 'edit_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'admin_reply_at' => 'datetime',
        'edited_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
