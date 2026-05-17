<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PendingInquiryResource extends JsonResource
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
            'name' => $this->warranty->user->name,
            'email'=> $this->warranty->user->email,
            'product' => Str::limit($this->warranty->product->name, 12, '...'),
            'inquiry_date' => $this->created_at->format('M d, Y')
        ];
    }
}
