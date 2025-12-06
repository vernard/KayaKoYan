<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageType;
use App\Events\ChatMessageSent;
use App\Events\MessagesRead;
use App\Events\UserTyping;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {}

    /**
     * Display the unified chat page with all conversations.
     */
    public function index(?Order $order = null): View
    {
        $user = auth()->user();
        $conversations = $this->chatService->getConversationsForUser($user);

        // If no order specified, select the first conversation
        if (!$order && $conversations->isNotEmpty()) {
            $order = $conversations->first();
        }

        // If order specified, verify user has access
        if ($order) {
            $this->authorize('chat', $order);
            $order->load(['listing', 'customer', 'worker', 'chatMessages.sender']);

            // Mark messages as read
            ChatMessage::where('order_id', $order->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        // Prepare conversations JSON for JavaScript
        $conversationsJson = $conversations->map(function ($c) use ($user) {
            $otherParticipant = $c->getOtherParticipant($user);
            $lastMessage = $c->chatMessages->last();
            return [
                'id' => $c->id,
                'other_participant_name' => $otherParticipant->name,
                'other_participant_avatar' => $otherParticipant->avatar_url,
                'order_number' => $c->order_number,
                'unread_count' => $c->unread_count ?? 0,
                'chat_enabled' => $c->isChatEnabled(),
                'status_label' => $c->status->label(),
                'status_color' => $c->status->color(),
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message,
                    'sender_id' => $lastMessage->sender_id,
                    'is_file' => $lastMessage->isFile(),
                    'is_delivery_notice' => $lastMessage->isDeliveryNotice(),
                ] : null,
            ];
        })->toArray();

        return view('chat.index', compact('conversations', 'order', 'conversationsJson'));
    }

    /**
     * Display chat for a specific order.
     */
    public function show(Order $order): View
    {
        return $this->index($order);
    }

    /**
     * Store a new chat message.
     */
    public function store(Request $request, Order $order): JsonResponse
    {
        $this->authorize('chat', $order);

        // Check if chat is enabled for this order
        if (!$order->isChatEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Chat is disabled for this order.',
            ], 403);
        }

        $validated = $request->validate([
            'message' => ['required_without:file', 'nullable', 'string', 'max:5000'],
            'file' => ['required_without:message', 'nullable', 'file', 'max:10240'],
        ]);

        $type = ChatMessageType::Text;
        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $type = ChatMessageType::File;
            $file = $request->file('file');
            $filePath = $file->store('chat-files/' . $order->id, 'public');
            $fileName = $file->getClientOriginalName();
        }

        $message = ChatMessage::create([
            'order_id' => $order->id,
            'sender_id' => auth()->id(),
            'message' => $validated['message'] ?? null,
            'type' => $type,
            'file_path' => $filePath,
            'file_name' => $fileName,
        ]);

        $message->load('sender');

        // Broadcast the message via WebSocket
        broadcast(new ChatMessageSent($message, $order))->toOthers();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Broadcast typing indicator.
     */
    public function typing(Request $request, Order $order): JsonResponse
    {
        $this->authorize('chat', $order);

        $validated = $request->validate([
            'is_typing' => ['required', 'boolean'],
        ]);

        broadcast(new UserTyping(
            $order,
            auth()->user(),
            $validated['is_typing']
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Mark messages as read and broadcast.
     */
    public function markRead(Order $order): JsonResponse
    {
        $this->authorize('chat', $order);

        $user = auth()->user();

        // Get unread message IDs before marking
        $unreadMessageIds = ChatMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->pluck('id')
            ->toArray();

        if (empty($unreadMessageIds)) {
            return response()->json(['success' => true, 'marked' => 0]);
        }

        // Mark messages as read
        ChatMessage::whereIn('id', $unreadMessageIds)
            ->update(['read_at' => now()]);

        // Broadcast read receipt
        broadcast(new MessagesRead(
            $order,
            $user,
            $unreadMessageIds
        ))->toOthers();

        return response()->json([
            'success' => true,
            'marked' => count($unreadMessageIds),
        ]);
    }

    /**
     * Get messages for a specific order (API endpoint).
     */
    public function messages(Order $order): JsonResponse
    {
        $this->authorize('chat', $order);

        $messages = $order->chatMessages()
            ->with('sender')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'type' => $message->type->value,
                    'file_path' => $message->file_path,
                    'file_name' => $message->file_name,
                    'file_url' => $message->file_url,
                    'created_at' => $message->created_at->toISOString(),
                    'is_own' => $message->sender_id === auth()->id(),
                ];
            });

        // Mark messages as read
        ChatMessage::where('order_id', $order->id)
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages,
            'chat_enabled' => $order->isChatEnabled(),
            'order_status' => $order->status->label(),
        ]);
    }

    /**
     * Get unread message count (API endpoint for nav badge).
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->chatService->getUnreadCount(auth()->user());

        return response()->json(['count' => $count]);
    }
}
