<x-filament-panels::page>
    <div class="h-[600px] flex rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm" x-data="workerChatPage()" x-init="init()">
        <!-- Conversations List (Left Panel) -->
        <div class="w-80 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Messages</h2>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template x-if="conversationsData.length === 0">
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p>No conversations yet</p>
                        <p class="text-sm mt-1">Customers will appear here when they message you</p>
                    </div>
                </template>
                <template x-for="conv in conversationsData" :key="conv.id">
                    <button type="button"
                       class="w-full text-left block p-4 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                       :class="{ 'bg-amber-50 dark:bg-amber-900/20 border-l-4 border-l-amber-500': selectedOrder === conv.id }"
                       @click="selectConversation(conv.id)">
                        <div class="flex items-start gap-3">
                            <img :src="conv.customer_avatar"
                                 :alt="conv.customer_name"
                                 class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white truncate" x-text="conv.customer_name"></span>
                                    <span x-show="conv.unread_count > 0" class="bg-red-500 text-white text-xs font-bold rounded-full h-5 min-w-5 flex items-center justify-center px-1" x-text="conv.unread_count"></span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate" x-text="conv.order_number"></p>
                                <template x-if="conv.last_message">
                                    <p class="text-sm text-gray-600 dark:text-gray-300 truncate mt-1">
                                        <span x-show="conv.last_message.sender_id === {{ auth()->id() }}">You: </span>
                                        <span x-text="conv.last_message.message || (conv.last_message.is_file ? 'Sent a file' : 'Delivery notice')"></span>
                                    </p>
                                </template>
                                <template x-if="!conv.chat_enabled">
                                    <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium"
                                          :class="conv.status_color === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'"
                                          x-text="conv.status"></span>
                                </template>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <!-- Chat Panel (Right) -->
        <div class="flex-1 flex flex-col bg-gray-50 dark:bg-gray-950 min-w-0">
            <!-- Chat content - shown when conversation selected -->
            <div x-show="selectedOrder" x-cloak class="flex flex-col h-full">
                <!-- Chat Header -->
                <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 p-4 flex-shrink-0">
                    <div class="flex items-center gap-4">
                        <img :src="otherParticipantAvatar"
                             :alt="otherParticipantName"
                             class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 dark:text-white truncate" x-text="otherParticipantName"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="orderNumber"></span>
                                <span class="mx-1">&bull;</span>
                                <span x-text="orderStatus"></span>
                                <span class="mx-1">&bull;</span>
                                <span x-show="isOtherOnline" class="text-green-600 dark:text-green-400">Online</span>
                                <span x-show="!isOtherOnline" class="text-gray-400">Offline</span>
                            </p>
                        </div>
                        <a :href="'/worker/orders/' + selectedOrder"
                           class="text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300 text-sm font-medium flex-shrink-0">
                            View Order
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-0">
                    <template x-for="message in messages" :key="message.id">
                        <div :class="message.is_own ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="message.is_own ? 'bg-amber-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white'" class="max-w-[70%] rounded-lg p-4 shadow-sm">
                                <template x-if="message.type === 'delivery_notice'">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span class="font-semibold">Delivery Notice</span>
                                    </div>
                                </template>
                                <template x-if="message.type === 'file'">
                                    <a :href="message.file_url" target="_blank" class="flex items-center gap-2 underline">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="message.file_name"></span>
                                    </a>
                                </template>
                                <p x-text="message.message" x-show="message.message"></p>
                                <p :class="message.is_own ? 'text-amber-200' : 'text-gray-500 dark:text-gray-400'" class="text-xs mt-2">
                                    <span x-text="message.sender_name"></span> &bull; <span x-text="formatTime(message.created_at)"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Typing Indicator -->
                <div x-show="isOtherTyping" x-transition class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                    <span x-text="otherTypingName"></span> is typing...
                </div>

                <!-- Chat Disabled Notice -->
                <div x-show="!chatEnabled" class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 p-4 text-center text-gray-600 dark:text-gray-400 flex-shrink-0">
                    <p class="text-sm">
                        This order has been <strong x-text="orderStatus ? orderStatus.toLowerCase() : ''"></strong>. Chat is now read-only.
                    </p>
                </div>

                <!-- Message Input -->
                <div x-show="chatEnabled" class="border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-900 flex-shrink-0">
                    <form @submit.prevent="sendMessage()" class="flex gap-4">
                        <input type="text" x-model="newMessage" @input="onInputChange()" placeholder="Type your message..."
                               class="flex-1 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 dark:bg-gray-800 dark:text-white">
                        <button type="submit" :disabled="!newMessage.trim()"
                                class="bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            Send
                        </button>
                    </form>
                </div>
            </div>

            <!-- No conversation selected -->
            <div x-show="!selectedOrder" class="flex-1 flex items-center justify-center text-gray-500 dark:text-gray-400">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-lg font-medium">Select a conversation</p>
                    <p class="text-sm mt-1">Choose a chat from the list to start messaging</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function workerChatPage() {
            return {
                selectedOrder: {{ $selectedOrder ? $selectedOrder->id : 'null' }},
                messages: [],
                newMessage: '',
                lastId: 0,
                chatEnabled: true,
                otherParticipantName: '',
                otherParticipantAvatar: '',
                orderNumber: '',
                orderStatus: '',
                conversationsData: @json($conversationsJson),

                // WebSocket-related properties
                channel: null,
                presenceChannel: null,
                isOtherTyping: false,
                otherTypingName: '',
                typingTimeout: null,
                typingDebounce: null,
                isOtherOnline: false,

                init() {
                    if (this.selectedOrder) {
                        this.loadConversation(this.selectedOrder);
                        this.subscribeToChannel();
                    }
                },

                loadConversation(orderId) {
                    const conversation = this.conversationsData.find(c => c.id === orderId);
                    if (conversation) {
                        this.messages = conversation.messages;
                        this.chatEnabled = conversation.chat_enabled;
                        this.otherParticipantName = conversation.customer_name;
                        this.otherParticipantAvatar = conversation.customer_avatar;
                        this.orderNumber = conversation.order_number;
                        this.orderStatus = conversation.status;
                        this.lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;
                        this.$nextTick(() => this.scrollToBottom());

                        // Mark messages as read
                        this.markMessagesAsRead();
                    }
                },

                selectConversation(orderId) {
                    if (this.selectedOrder === orderId) return;

                    // Unsubscribe from previous channel
                    this.unsubscribeFromChannel();

                    this.selectedOrder = orderId;
                    this.isOtherTyping = false;
                    this.isOtherOnline = false;
                    this.loadConversation(orderId);
                    this.subscribeToChannel();
                },

                subscribeToChannel() {
                    if (!this.selectedOrder || !window.Echo) return;

                    const currentUserId = {{ auth()->id() }};

                    // Subscribe to private chat channel
                    this.channel = window.Echo.private(`order.${this.selectedOrder}.chat`)
                        .listen('.message.sent', (data) => {
                            this.handleNewMessage(data, currentUserId);
                        })
                        .listen('.user.typing', (data) => {
                            this.handleTypingIndicator(data, currentUserId);
                        })
                        .listen('.messages.read', (data) => {
                            this.handleReadReceipt(data, currentUserId);
                        });

                    // Subscribe to presence channel for online status
                    this.presenceChannel = window.Echo.join(`order.${this.selectedOrder}.presence`)
                        .here((users) => {
                            const otherUser = users.find(u => u.id !== currentUserId);
                            this.isOtherOnline = !!otherUser;
                        })
                        .joining((user) => {
                            if (user.id !== currentUserId) {
                                this.isOtherOnline = true;
                            }
                        })
                        .leaving((user) => {
                            if (user.id !== currentUserId) {
                                this.isOtherOnline = false;
                            }
                        });
                },

                unsubscribeFromChannel() {
                    if (this.channel) {
                        window.Echo.leave(`order.${this.selectedOrder}.chat`);
                        this.channel = null;
                    }
                    if (this.presenceChannel) {
                        window.Echo.leave(`order.${this.selectedOrder}.presence`);
                        this.presenceChannel = null;
                    }
                },

                handleNewMessage(data, currentUserId) {
                    // Check if message already exists
                    if (this.messages.find(m => m.id === data.id)) return;

                    const message = {
                        ...data,
                        is_own: data.sender_id === currentUserId,
                    };

                    this.messages.push(message);
                    this.lastId = message.id;
                    this.$nextTick(() => this.scrollToBottom());

                    // Update conversation preview
                    this.updateConversationPreview(this.selectedOrder, message);

                    // Mark as read if we're viewing this conversation
                    if (!message.is_own) {
                        this.markMessagesAsRead();
                    }
                },

                handleTypingIndicator(data, currentUserId) {
                    if (data.user_id === currentUserId) return;

                    this.isOtherTyping = data.is_typing;
                    this.otherTypingName = data.user_name;

                    // Auto-clear typing indicator after 3 seconds
                    if (data.is_typing) {
                        clearTimeout(this.typingTimeout);
                        this.typingTimeout = setTimeout(() => {
                            this.isOtherTyping = false;
                        }, 3000);
                    }
                },

                handleReadReceipt(data, currentUserId) {
                    if (data.reader_id === currentUserId) return;

                    // Update read status on messages
                    data.message_ids.forEach(id => {
                        const message = this.messages.find(m => m.id === id);
                        if (message) {
                            message.read_at = data.read_at;
                        }
                    });
                },

                async sendMessage() {
                    if (!this.newMessage.trim() || !this.chatEnabled) return;

                    const formData = new FormData();
                    formData.append('message', this.newMessage);

                    try {
                        const response = await fetch(`/worker/chats/${this.selectedOrder}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const message = data.message;

                            // Add message to display immediately (optimistic update)
                            const newMsg = {
                                id: message.id,
                                sender_id: message.sender_id,
                                sender_name: message.sender.name,
                                message: message.message,
                                type: message.type,
                                file_path: message.file_path,
                                file_name: message.file_name,
                                file_url: message.file_url,
                                created_at: message.created_at,
                                is_own: true,
                                read_at: null,
                            };

                            if (!this.messages.find(m => m.id === message.id)) {
                                this.messages.push(newMsg);
                                this.lastId = message.id;
                                this.$nextTick(() => this.scrollToBottom());
                            }

                            // Update conversation preview
                            this.updateConversationPreview(this.selectedOrder, newMsg);

                            this.newMessage = '';

                            // Stop typing indicator
                            this.sendTypingIndicator(false);
                        }
                    } catch (error) {
                        console.error('Failed to send message:', error);
                    }
                },

                // Debounced typing indicator
                onInputChange() {
                    this.sendTypingIndicator(true);

                    clearTimeout(this.typingDebounce);
                    this.typingDebounce = setTimeout(() => {
                        this.sendTypingIndicator(false);
                    }, 2000);
                },

                async sendTypingIndicator(isTyping) {
                    if (!this.selectedOrder || !this.chatEnabled) return;

                    try {
                        await fetch(`/worker/chats/${this.selectedOrder}/typing`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ is_typing: isTyping })
                        });
                    } catch (error) {
                        // Silently fail for typing indicators
                    }
                },

                async markMessagesAsRead() {
                    if (!this.selectedOrder) return;

                    try {
                        await fetch(`/worker/chats/${this.selectedOrder}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            }
                        });
                    } catch (error) {
                        console.error('Failed to mark messages as read:', error);
                    }
                },

                scrollToBottom() {
                    const container = this.$refs.messagesContainer;
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                },

                formatTime(isoString) {
                    const date = new Date(isoString);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
                           date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                },

                updateConversationPreview(orderId, message) {
                    const conv = this.conversationsData.find(c => c.id === orderId);
                    if (conv) {
                        conv.last_message = {
                            message: message.message,
                            sender_id: message.sender_id,
                            is_file: message.type === 'file',
                            is_delivery_notice: message.type === 'delivery_notice',
                        };
                        // If receiving a message (not own), increment unread count for non-selected conversations
                        if (!message.is_own && orderId !== this.selectedOrder) {
                            conv.unread_count = (conv.unread_count || 0) + 1;
                        }
                        // Clear unread count for selected conversation
                        if (orderId === this.selectedOrder) {
                            conv.unread_count = 0;
                        }
                    }
                }
            }
        }
    </script>
</x-filament-panels::page>
