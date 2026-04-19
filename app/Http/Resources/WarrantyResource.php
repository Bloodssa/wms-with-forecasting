<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarrantyResource extends JsonResource
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
            'purchase_date' => $this->purchase_date,
            'expiry_date' => $this->expiry_date,
            'product' => [
                'name' => $this->product->name,
                'image_url' => $this->product->image_url,
            ],
            'user' => [
                'name' => $this->user?->name,
            ]
        ];
    }
}
