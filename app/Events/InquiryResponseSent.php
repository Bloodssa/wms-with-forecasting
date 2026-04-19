<?php

namespace App\Events;

use App\Models\InquiryResponse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InquiryResponseSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public InquiryResponse $response)
    {
        $this->response->load('user');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // channels based on inquiry id
        return [
            new PrivateChannel('inquiry.' . $this->response->warranty_inquiries_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ResponseSent';
    }
}
