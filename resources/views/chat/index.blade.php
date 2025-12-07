<x-layouts.app>
    <x-slot:title>Chat</x-slot:title>

    <audio id="notification-sound" src="/sounds/notification.mp3" preload="auto"></audio>

    <div class="h-[70vh] max-w-7xl mx-auto mt-8 mb-4 flex" x-data="chatPage()" x-init="init()">
        <!-- Conversations List (Left Panel) -->
        <div class="w-80 border-r border-gray-200 bg-white flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-lg font-semibold text-gray-900">Messages</h1>
                <!-- Filter Toggle -->
                <div class="mt-2 flex rounded-lg bg-gray-100 p-1">
                    <button type="button"
                            @click="showOngoingOnly = false"
                            :class="!showOngoingOnly ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                        All
                    </button>
                    <button type="button"
                            @click="showOngoingOnly = true"
                            :class="showOngoingOnly ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                        Ongoing
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template x-if="getFilteredConversations().length === 0">
                    <div class="p-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p>No conversations yet</p>
                        <p class="text-sm mt-1">Start chatting when you have an order</p>
                    </div>
                </template>
                <template x-for="conv in getFilteredConversations()" :key="conv.id">
                    <a :href="'/chats/' + conv.id"
                       class="block p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors"
                       :class="{ 'bg-amber-50 border-l-4 border-l-amber-500': selectedOrder === conv.id }"
                       @click.prevent="selectConversation(conv.id)">
                        <div class="flex items-start gap-3">
                            <img :src="conv.other_participant_avatar"
                                 :alt="conv.other_participant_name"
                                 class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-900 truncate" :class="conv.unread_count > 0 ? 'font-bold' : 'font-medium'">
                                        <span x-text="conv.other_participant_name"></span>
                                        <span x-show="conv.unread_count > 0" class="text-amber-600 text-sm" x-text="'(' + conv.unread_count + ')'"></span>
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 truncate" x-text="conv.order_number"></p>
                                <template x-if="conv.last_message">
                                    <p class="text-sm text-gray-600 truncate mt-1">
                                        <span x-show="conv.last_message.sender_id === {{ auth()->id() }}">You: </span>
                                        <span x-text="conv.last_message.message || (conv.last_message.is_file ? 'Sent a file' : 'Delivery notice')"></span>
                                    </p>
                                </template>
                                <template x-if="!conv.chat_enabled">
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium"
                                          :class="conv.status_color === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                          x-text="conv.status_label"></span>
                                </template>
                            </div>
                        </div>
                    </a>
                </template>
            </div>
        </div>

        <!-- Chat Panel (Right) -->
        <div class="flex-1 flex flex-col bg-gray-50">
            @if($order)
                @php
                    $otherParticipant = $order->getOtherParticipant(auth()->user());
                @endphp

                <!-- Chat Header -->
                <div class="bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center gap-4">
                        <button @click="selectedOrder = null" class="md:hidden text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                        </button>
                        <img :src="otherParticipantAvatar || '{{ $otherParticipant->avatar_url }}'"
                             :alt="otherParticipantName || '{{ $otherParticipant->name }}'"
                             class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1">
                            <h2 class="font-semibold text-gray-900" x-text="otherParticipantName || '{{ $otherParticipant->name }}'">{{ $otherParticipant->name }}</h2>
                            <p class="text-sm text-gray-500">
                                <span x-text="orderNumber || '{{ $order->order_number }}'">{{ $order->order_number }}</span>
                                <span class="mx-1">&bull;</span>
                                <span x-text="orderStatus || '{{ $order->status->label() }}'">{{ $order->status->label() }}</span>
                                <span class="mx-1">&bull;</span>
                                <span x-show="isOtherOnline" class="text-green-600">Online</span>
                                <span x-show="!isOtherOnline" class="text-gray-400">Offline</span>
                            </p>
                        </div>
                        <a href="{{ route('customer.orders.show', $order) }}"
                           class="text-amber-600 hover:text-amber-700 text-sm font-medium">
                            View Order
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Server-rendered messages -->
                    <template x-if="!messagesLoaded">
                        @foreach($order->chatMessages as $message)
                            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[70%] {{ $message->sender_id === auth()->id() ? 'bg-amber-600 text-white' : 'bg-white text-gray-900' }} rounded-lg p-4 shadow-sm">
                                    @if($message->isDeliveryNotice())
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                            </svg>
                                            <span class="font-semibold">Delivery Notice</span>
                                        </div>
                                    @endif
                                    @if($message->isFile())
                                        @if($message->isImage())
                                            <div class="cursor-pointer" @click="openImageModal('{{ $message->file_url }}', '{{ $message->file_name }}', '{{ $message->formatted_file_size }}', '{{ $message->created_at->format('M d, Y g:i A') }}')">
                                                <img src="{{ $message->file_url }}" alt="{{ $message->file_name }}" class="max-w-full rounded-lg max-h-64 object-contain hover:opacity-90 transition-opacity">
                                            </div>
                                        @else
                                            <div class="flex items-start gap-3 p-3 bg-gray-100 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-amber-500/20' : '' }}">
                                                <svg class="w-10 h-10 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-medium truncate">{{ $message->file_name }}</p>
                                                    <p class="text-sm opacity-70">{{ $message->formatted_file_size }} &bull; {{ $message->created_at->format('M d, Y') }}</p>
                                                    <a href="{{ $message->file_url }}" download="{{ $message->file_name }}" class="inline-flex items-center gap-1 mt-2 text-sm font-medium {{ $message->sender_id === auth()->id() ? 'text-white hover:text-amber-200' : 'text-amber-600 hover:text-amber-700' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                        Download
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if($message->message)
                                        <p>{{ $message->message }}</p>
                                    @endif
                                    <p class="text-xs {{ $message->sender_id === auth()->id() ? 'text-amber-200' : 'text-gray-500' }} mt-2">
                                        {{ $message->sender->name }} &bull; {{ $message->created_at->format('M d, g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </template>

                    <!-- Dynamic messages (after switching conversations) -->
                    <template x-for="message in messages" :key="message.id">
                        <div :class="message.is_own ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="message.is_own ? 'bg-amber-600 text-white' : 'bg-white text-gray-900'" class="max-w-[70%] rounded-lg p-4 shadow-sm">
                                <template x-if="message.type === 'delivery_notice'">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span class="font-semibold">Delivery Notice</span>
                                    </div>
                                </template>
                                <template x-if="message.type === 'file' && message.is_image">
                                    <div class="cursor-pointer" @click="openImageModal(message.file_url, message.file_name, message.formatted_file_size, formatTime(message.created_at))">
                                        <img :src="message.file_url" :alt="message.file_name" class="max-w-full rounded-lg max-h-64 object-contain hover:opacity-90 transition-opacity">
                                    </div>
                                </template>
                                <template x-if="message.type === 'file' && !message.is_image">
                                    <div class="flex items-start gap-3 p-3 rounded-lg" :class="message.is_own ? 'bg-amber-500/20' : 'bg-gray-100'">
                                        <svg class="w-10 h-10 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate" x-text="message.file_name"></p>
                                            <p class="text-sm opacity-70">
                                                <span x-text="message.formatted_file_size || formatFileSize(message.file_size)"></span>
                                                <span class="mx-1">&bull;</span>
                                                <span x-text="formatTime(message.created_at).split(',')[0]"></span>
                                            </p>
                                            <a :href="message.file_url" :download="message.file_name" class="inline-flex items-center gap-1 mt-2 text-sm font-medium" :class="message.is_own ? 'text-white hover:text-amber-200' : 'text-amber-600 hover:text-amber-700'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </template>
                                <p x-text="message.message" x-show="message.message"></p>
                                <p :class="message.is_own ? 'text-amber-200' : 'text-gray-500'" class="text-xs mt-2">
                                    <span x-text="message.sender_name"></span> &bull; <span x-text="formatTime(message.created_at)"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Typing Indicator -->
                <div x-show="isOtherTyping" x-transition class="px-4 py-2 text-sm text-gray-500 italic bg-gray-50 border-t border-gray-100">
                    <span x-text="otherTypingName"></span> is typing...
                </div>

                <!-- Chat Disabled Notice - shown when chatEnabled is false -->
                <div x-show="!chatEnabled" class="bg-gray-100 border-t border-gray-200 p-4 text-center text-gray-600">
                    <p class="text-sm">
                        This order has been <strong x-text="orderStatus ? orderStatus.toLowerCase() : '{{ strtolower($order->status->label()) }}'"></strong>. Chat is now read-only.
                    </p>
                </div>

                <!-- Message Input - shown when chatEnabled is true -->
                <div x-show="chatEnabled" class="border-t border-gray-200 p-4 bg-white">
                    <x-chat.message-input />
                </div>
            @else
                <!-- No conversation selected -->
                <div class="flex-1 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-lg font-medium">Select a conversation</p>
                        <p class="text-sm mt-1">Choose a chat from the list to start messaging</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Image Modal (shared component) -->
        <x-chat.image-modal />
    </div>

    <script>
        function chatPage() {
            // Use shared chat component with customer-specific config
            const shared = window.createChatComponent({
                apiBasePath: '/chats',
                conversationsKey: 'conversations',
                participantPrefix: 'other_participant',
                currentUserId: {{ auth()->id() }},
            });

            return {
                // Spread shared state and methods
                ...shared,

                // Customer-specific state
                selectedOrder: {{ $order ? $order->id : 'null' }},
                lastId: {{ $order ? ($order->chatMessages->last()?->id ?? 0) : 0 }},
                chatEnabled: {{ $order ? ($order->isChatEnabled() ? 'true' : 'false') : 'true' }},
                conversations: @json($conversationsJson),
                messagesLoaded: false,
                showOngoingOnly: false,

                // Filter conversations based on toggle
                getFilteredConversations() {
                    if (!this.showOngoingOnly) return this.conversations;
                    return this.conversations.filter(c => c.chat_enabled);
                },

                // Customer-specific init
                init() {
                    // Initialize online status listener (from shared)
                    this.initOnlineStatusListener();

                    // Subscribe to notification channel for unread count updates
                    if (window.Echo) {
                        window.Echo.private(`user.{{ auth()->id() }}.notifications`)
                            .listen('.unread.updated', (data) => {
                                window.updateUnreadTitle?.(data.count);
                            });

                        // Subscribe to all conversation channels (from shared)
                        this.subscribeToAllConversations();
                    }

                    // Initialize title with unread count (from shared)
                    this.initUnreadTitle();

                    // Set initial otherParticipantId if order is selected
                    if (this.selectedOrder) {
                        const conversation = this.getConversations().find(c => c.id === this.selectedOrder);
                        if (conversation) {
                            this.otherParticipantId = this.getParticipantId(conversation);
                            this.isOtherOnline = window.onlineUsers?.has(this.otherParticipantId) || false;
                            conversation.unread_count = 0;
                        }
                        // Fetch fresh messages from API
                        this.fetchMessages(this.selectedOrder);
                    }
                },

                // Customer-specific: Select conversation with URL update
                async selectConversation(orderId) {
                    if (this.selectedOrder === orderId) return;

                    this.selectedOrder = orderId;
                    this.messages = [];
                    this.lastId = 0;
                    this.isOtherTyping = false;
                    this.isOtherOnline = false;

                    // Update URL without reload
                    history.pushState({}, '', `/chats/${orderId}`);

                    // Fetch messages for new conversation
                    await this.fetchMessages(orderId);
                },

                // Customer-specific: Fetch messages from API
                async fetchMessages(orderId) {
                    try {
                        const response = await fetch(`/chats/${orderId}/messages`);
                        const data = await response.json();

                        this.messages = data.messages;
                        this.messagesLoaded = true;
                        this.chatEnabled = data.chat_enabled;
                        this.orderStatus = data.order_status;

                        if (this.messages.length > 0) {
                            this.lastId = this.messages[this.messages.length - 1].id;
                        }

                        // Update header info from conversations data
                        const conversation = this.getConversations().find(c => c.id === orderId);
                        if (conversation) {
                            this.otherParticipantName = this.getParticipantName(conversation);
                            this.otherParticipantAvatar = this.getParticipantAvatar(conversation);
                            this.orderNumber = conversation.order_number;
                            this.otherParticipantId = this.getParticipantId(conversation);
                            this.isOtherOnline = window.onlineUsers?.has(this.otherParticipantId) || false;
                            conversation.unread_count = 0;

                            // Update header badge with new total unread count
                            const totalUnread = this.getConversations().reduce((sum, c) => sum + (c.unread_count || 0), 0);
                            window.updateUnreadTitle?.(totalUnread);
                        }

                        this.$nextTick(() => this.scrollToBottom());
                        this.markMessagesAsRead();
                    } catch (error) {
                        console.error('Failed to fetch messages:', error);
                    }
                },
            }
        }
    </script>
</x-layouts.app>
