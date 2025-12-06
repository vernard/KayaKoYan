<?php

namespace App\Filament\Worker\Pages;

use App\Models\Order;
use App\Services\ChatService;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class Chat extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Chat';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Chat';

    protected string $view = 'filament.worker.pages.chat';

    public ?Order $selectedOrder = null;

    public Collection $conversations;

    public array $conversationsJson = [];

    public function mount(?Order $order = null): void
    {
        $chatService = app(ChatService::class);
        $user = auth()->user();

        // Get all conversations for this worker
        $this->conversations = $chatService->getConversationsForUser($user);

        // Load chat messages for each conversation
        $this->conversations->load('chatMessages.sender');

        // Select order if provided, otherwise select first conversation
        if ($order && $order->worker_id === $user->id) {
            $this->selectedOrder = $order;
        } elseif ($this->conversations->isNotEmpty()) {
            $this->selectedOrder = $this->conversations->first();
        }

        // Mark messages as read for selected order
        if ($this->selectedOrder) {
            $this->selectedOrder->chatMessages()
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        // Prepare JSON data for JavaScript
        $this->conversationsJson = $this->conversations->map(function ($c) use ($user) {
            $lastMessage = $c->chatMessages->last();
            return [
                'id' => $c->id,
                'customer_name' => $c->customer->name,
                'customer_avatar' => $c->customer->avatar_url,
                'order_number' => $c->order_number,
                'status' => $c->status->label(),
                'status_color' => $c->status->color(),
                'chat_enabled' => $c->isChatEnabled(),
                'unread_count' => $c->unread_count ?? 0,
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message,
                    'sender_id' => $lastMessage->sender_id,
                    'is_file' => $lastMessage->isFile(),
                    'is_delivery_notice' => $lastMessage->isDeliveryNotice(),
                ] : null,
                'messages' => $c->chatMessages->map(function ($m) use ($user) {
                    return [
                        'id' => $m->id,
                        'sender_id' => $m->sender_id,
                        'sender_name' => $m->sender->name,
                        'message' => $m->message,
                        'type' => $m->type->value,
                        'file_path' => $m->file_path,
                        'file_name' => $m->file_name,
                        'file_url' => $m->file_path ? asset('storage/' . $m->file_path) : null,
                        'created_at' => $m->created_at->toISOString(),
                        'is_own' => $m->sender_id === $user->id,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        $count = app(ChatService::class)->getUnreadCount($user);

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Unread messages';
    }
}
