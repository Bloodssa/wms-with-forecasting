<?php

namespace App\Http\Resources\Warranty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'is_claimed' => (bool) $this->is_claimed,
            'claim_email' => $this->claim_email,
            'purchase_date' => $this->purchase_date,
            'expiry_date' => $this->expiry_date,
            'user_id' => $this->user_id,
            'user' => [
                'name' => $this->user?->name,
            ],
            'product' => [
                'name' => $this->product?->name,
                'image_url' => $this->product?->image_url,
            ],
        ];
    }
}
