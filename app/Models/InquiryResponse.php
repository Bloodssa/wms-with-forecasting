<?php

namespace App\Models;

use App\Enum\InquiryResponseType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InquiryResponse extends Model
{
    protected $fillable = [
        'user_id',
        'warranty_inquiries_id',
        'message',
        'type',
        'attachments',
        'read_at'
    ];

    protected $casts = [
        'attachments' => 'array',
        'read_at' => 'datetime'
    ];

    // protected $touches = ['warrantyInquiries'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warrantyInquiries(): BelongsTo
    {
        return $this->belongsTo(WarrantyInquiries::class);
    }
}
