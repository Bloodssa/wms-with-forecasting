<?php

namespace App\Http\Resources\Warranty;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class WarrantyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $deleteAt = Carbon::parse($this->archived_at)->addDays(60);

        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'claim_email' => $this->claim_email,
            'status' => $this->status,
            'purchase_date' => $this->purchase_date,
            'expiry_date' => $this->expiry_date,
            'is_claimed' => $this->is_claimed,
            'archived_at' => $this->archived_at,
            'delete_due_at' => $this->archived_at ? Carbon::parse($this->archived_at)->addDays(60) : null,
            'days_left' => $deleteAt
                ? max(0, floor(now()->diffInDays($deleteAt)))
                : null,
            'product' => [
                'name' =>  Str::limit($this->product->name, 12, '...'),
                'image_url' => $this->product->image_url,
            ],
            'user' => [
                'name' => $this->user?->name,
            ]
        ];
    }
}
