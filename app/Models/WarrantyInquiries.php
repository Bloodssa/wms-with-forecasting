<?php

namespace App\Models;

use App\Enum\InquiryStatusType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarrantyInquiries extends Model
{
    protected $fillable = [
        'user_id',
        'warranty_id',
        'message',
        'status',
        'attachments'
    ];

    protected $casts = [
        'status' => InquiryStatusType::class,
        'attachments' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(InquiryResponse::class);
    }
}
