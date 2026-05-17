<?php

namespace App\Http\Resources\Warranty;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class WarrantyInquiries extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {   
        $localDate = $this->created_at->timezone('Asia/Manila');

        return [
            'id' => $this->id,
            'message' => Str::limit($this->message, 15, '...'),
            'status' => $this->status,
            'unread_messages_count' => $this->unread_messages_count,
            'submitted_at' => $localDate->gt(now()->subDay()) 
                ? $localDate->diffForHumans() : $localDate->format('M d, Y'),
            'user' => [
                'name' => $this->warranty->user?->name,
                'email' => $this->warranty->user->email
            ],
            'warranty' => [
                'serial_number' => $this->warranty->serial_number,
                'product' => [
                    'name' => $this->warranty->product->name
                ]
            ]
        ];
    }
}
