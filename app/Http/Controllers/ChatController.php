<?php

namespace App\Http\Controllers;

use App\Enums\ChatMessageType;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        return response()->json([
            'success' => true,
            'message' => $message->load('sender'),
        ]);
    }

    /**
     * Stream chat messages via SSE.
     */
    public function stream(Order $order): StreamedResponse
    {
        $this->authorize('chat', $order);

        return response()->stream(function () use ($order) {
            $lastId = request()->query('last_id', 0);
            $timeout = 0;
            $maxTimeout = 30;

            while ($timeout < $maxTimeout) {
                $messages = ChatMessage::with('sender')
                    ->where('order_id', $order->id)
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->get();

                if ($messages->isNotEmpty()) {
                    $lastId = $messages->last()->id;

                    foreach ($messages as $message) {
                        if ($message->sender_id !== auth()->id()) {
                            $message->markAsRead();
                        }

                        $data = [
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

                        echo "data: " . json_encode($data) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                if (connection_aborted()) {
                    break;
                }

                sleep(1);
                $timeout += 1;
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
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
