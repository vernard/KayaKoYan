<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UnreadCountUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public int $count
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->userId . '.notifications'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'count' => $this->count,
        ];
    }

    public function broadcastAs(): string
    {
        return 'unread.updated';
    }
}
