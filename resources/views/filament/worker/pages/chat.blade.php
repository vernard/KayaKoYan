<x-filament-panels::page>
    <audio id="notification-sound" src="/sounds/notification.mp3" preload="auto"></audio>

    <div class="h-[600px] flex rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm" x-data="workerChatPage()" x-init="init()">
        <!-- Conversations List (Left Panel) -->
        <div class="w-80 flex-shrink-0 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 flex flex-col">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Messages</h2>
                <!-- Filter Toggle -->
                <div class="mt-2 flex rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button type="button"
                            @click="showOngoingOnly = false"
                            :class="!showOngoingOnly ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                        All
                    </button>
                    <button type="button"
                            @click="showOngoingOnly = true"
                            :class="showOngoingOnly ? 'bg-white dark:bg-gray-700 shadow text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="flex-1 px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                        Ongoing
                    </button>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">
                <template x-if="getFilteredConversations().length === 0">
                    <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                        <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p>No conversations yet</p>
                        <p class="text-sm mt-1">Customers will appear here when they message you</p>
                    </div>
                </template>
                <template x-for="conv in getFilteredConversations()" :key="conv.id">
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
                                    <span class="text-gray-900 dark:text-white truncate" :class="conv.unread_count > 0 ? 'font-bold' : 'font-medium'">
                                        <span x-text="conv.customer_name"></span>
                                        <span x-show="conv.unread_count > 0" class="text-amber-600 text-sm" x-text="'(' + conv.unread_count + ')'"></span>
                                    </span>
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
                                <template x-if="message.type === 'file' && message.is_image">
                                    <div class="cursor-pointer" @click="openImageModal(message.file_url, message.file_name, message.formatted_file_size, formatTime(message.created_at))">
                                        <img :src="message.file_url" :alt="message.file_name" class="max-w-full rounded-lg max-h-64 object-contain hover:opacity-90 transition-opacity">
                                    </div>
                                </template>
                                <template x-if="message.type === 'file' && !message.is_image">
                                    <div class="flex items-start gap-3 p-3 rounded-lg" :class="message.is_own ? 'bg-amber-500/20' : 'bg-gray-100 dark:bg-gray-700'">
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
                    <x-chat.message-input :dark-mode="true" />
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

        <!-- Image Modal (shared component) -->
        <x-chat.image-modal />
    </div>

    <script>
        function workerChatPage() {
            // Use shared chat component with worker-specific config
            const shared = window.createChatComponent({
                apiBasePath: '/worker/chats',
                conversationsKey: 'conversationsData',
                participantPrefix: 'customer',
                currentUserId: {{ auth()->id() }},
            });

            return {
                // Spread shared state and methods
                ...shared,

                // Worker-specific state
                selectedOrder: {{ $selectedOrder ? $selectedOrder->id : 'null' }},
                conversationsData: @json($conversationsJson),
                showOngoingOnly: false,

                // Filter conversations based on toggle
                getFilteredConversations() {
                    if (!this.showOngoingOnly) return this.conversationsData;
                    return this.conversationsData.filter(c => c.chat_enabled);
                },

                // Worker-specific init
                init() {
                    // Initialize online status listener (from shared)
                    this.initOnlineStatusListener();

                    // Subscribe to notification channel for unread count updates and new conversations
                    if (window.Echo) {
                        window.Echo.private(`user.{{ auth()->id() }}.notifications`)
                            .listen('.unread.updated', (data) => {
                                window.updateUnreadTitle?.(data.count);
                            })
                            .listen('.conversation.new', (data) => {
                                this.handleNewConversation(data);
                            });

                        // Subscribe to all conversation channels (from shared)
                        this.subscribeToAllConversations();
                    }

                    // Initialize title with unread count (from shared)
                    this.initUnreadTitle();

                    if (this.selectedOrder) {
                        this.loadConversation(this.selectedOrder);
                    }
                },

                // Worker-specific: Load conversation from local data (no API fetch)
                loadConversation(orderId) {
                    const conversation = this.getConversations().find(c => c.id === orderId);
                    if (conversation) {
                        // Use shared helper to load data
                        this.loadConversationData(conversation);

                        // Mark messages as read and reset unread count
                        conversation.unread_count = 0;
                        this.markMessagesAsRead();

                        // Update sidebar badge with new total unread count
                        const totalUnread = this.getConversations().reduce((sum, c) => sum + (c.unread_count || 0), 0);
                        window.updateUnreadTitle?.(totalUnread);
                    }
                },

                // Worker-specific: Select conversation (simpler, no URL update)
                selectConversation(orderId) {
                    if (this.selectedOrder === orderId) return;

                    this.selectedOrder = orderId;
                    this.isOtherTyping = false;
                    this.isOtherOnline = false;
                    this.loadConversation(orderId);
                },

                // Worker-specific: Handle new conversation from customer
                handleNewConversation(data) {
                    const conversations = this.getConversations();

                    // Check if conversation already exists
                    if (conversations.find(c => c.id === data.id)) return;

                    // Add new conversation to the top of the list
                    conversations.unshift({
                        id: data.id,
                        customer_id: data.customer_id,
                        customer_name: data.customer_name,
                        customer_avatar: data.customer_avatar,
                        order_number: data.order_number,
                        status: data.status,
                        status_color: data.status_color,
                        chat_enabled: data.chat_enabled,
                        unread_count: data.unread_count,
                        last_message: data.last_message,
                        messages: data.messages || [],
                    });

                    // Subscribe to this new conversation's channel
                    this.subscribeToConversation(data.id);

                    // Play notification sound
                    document.getElementById('notification-sound')?.play().catch(() => {});

                    // Update title with new unread count
                    const totalUnread = conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
                    window.updateUnreadTitle?.(totalUnread);
                }
            }
        }
    </script>
</x-filament-panels::page>
