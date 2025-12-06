<?php

namespace App\Events;

use App\Models\Order;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessagesRead implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public User $reader,
        public array $messageIds
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->order->id . '.chat'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'reader_id' => $this->reader->id,
            'reader_name' => $this->reader->name,
            'message_ids' => $this->messageIds,
            'read_at' => now()->toISOString(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }
}
