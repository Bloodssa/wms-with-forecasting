<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'brand' => $this->whenHas('brand'),
            'warranty_duration' => $this->whenHas('warranty_duration'),
            'image_url' => $this->whenHas('product_image_url'),
            'service_center_name' => $this->whenHas('service_center_name'),
            'service_center_address' => $this->whenHas('service_center_address'),
            'category' => $this->whenLoaded('category', fn() => [
                'name' => $this->category->name,
            ]),
        ];
    }
}
