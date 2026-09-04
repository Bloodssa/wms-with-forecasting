<?php

namespace App\Models;

use App\Enum\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarrantyServiceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'warranty_inquiries_id',
        'recorded_by',
        'service_type',
        'parts_cost',
        'labor_cost',
        'total_cost',
        'notes',
    ];

    protected $casts = [
        'service_type' => ServiceType::class,
        'parts_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(WarrantyInquiries::class, 'warranty_inquiries_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
