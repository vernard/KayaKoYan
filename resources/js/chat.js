/**
 * Shared chat functionality for customer and worker chat pages.
 * This module extracts common chat logic to prevent code duplication.
 *
 * Usage:
 *   import { createChatComponent } from './chat.js';
 *
 *   function chatPage() {
 *       return {
 *           ...createChatComponent({
 *               apiBasePath: '/chats',
 *               conversationsKey: 'conversations',
 *               participantPrefix: 'other_participant',
 *               currentUserId: 123,
 *           }),
 *           // Page-specific state and methods...
 *       };
 *   }
 */

export function createChatComponent(config) {
    const {
        apiBasePath,        // '/chats' or '/worker/chats'
        conversationsKey,   // 'conversations' or 'conversationsData'
        participantPrefix,  // 'other_participant' or 'customer'
        currentUserId,      // The authenticated user's ID
    } = config;

    return {
        // =========================================
        // Shared State
        // =========================================
        selectedOrder: null,
        messages: [],
        newMessage: '',
        lastId: 0,
        chatEnabled: true,
        otherParticipantName: '',
        otherParticipantAvatar: '',
        orderNumber: '',
        orderStatus: '',

        // WebSocket-related properties
        channel: null,
        presenceChannel: null,
        isOtherTyping: false,
        otherTypingName: '',
        typingTimeout: null,
        typingDebounce: null,
        lastTypingSent: 0,
        isOtherOnline: false,
        otherParticipantId: null,
        subscribedChannels: new Set(),

        // File attachment properties
        selectedFile: null,
        previewUrl: null,

        // Image modal properties
        modalImage: null,
        modalFileName: '',
        modalFileSize: '',
        modalFileDate: '',
        zoomLevel: 1,

        // =========================================
        // Helper: Get conversations array
        // =========================================
        getConversations() {
            return this[conversationsKey];
        },

        // =========================================
        // Helper: Get participant properties from conversation
        // =========================================
        getParticipantName(conv) {
            return conv[`${participantPrefix}_name`];
        },

        getParticipantAvatar(conv) {
            return conv[`${participantPrefix}_avatar`];
        },

        getParticipantId(conv) {
            return conv[`${participantPrefix}_id`];
        },

        // =========================================
        // WebSocket: Subscribe to conversation channel
        // =========================================
        subscribeToConversation(orderId) {
            if (!window.Echo) return;

            // Prevent double-subscription
            if (this.subscribedChannels.has(orderId)) return;
            this.subscribedChannels.add(orderId);

            window.Echo.private(`order.${orderId}.chat`)
                .listen('.message.sent', (data) => {
                    if (orderId === this.selectedOrder) {
                        this.handleNewMessage(data, currentUserId);
                    } else {
                        this.handleOtherConversationMessage(orderId, data);
                    }
                })
                .listen('.user.typing', (data) => {
                    if (orderId === this.selectedOrder) {
                        this.handleTypingIndicator(data, currentUserId);
                    }
                })
                .listen('.messages.read', (data) => {
                    if (orderId === this.selectedOrder) {
                        this.handleReadReceipt(data, currentUserId);
                    }
                });
        },

        // =========================================
        // Handler: New message in current conversation
        // =========================================
        handleNewMessage(data, userId) {
            // Check if message already exists
            if (this.messages.find(m => m.id === data.id)) return;

            const message = {
                ...data,
                is_own: data.sender_id === userId,
            };

            this.messages.push(message);
            this.lastId = message.id;
            this.$nextTick(() => this.scrollToBottom());

            // Update conversation preview
            this.updateConversationPreview(this.selectedOrder, message);

            // If message is from someone else, mark as read (no sound since user is viewing this chat)
            if (!message.is_own) {
                this.markMessagesAsRead();
            }
        },

        // =========================================
        // Handler: New message in OTHER conversation (not currently viewing)
        // =========================================
        handleOtherConversationMessage(orderId, data) {
            const conversations = this.getConversations();
            const conv = conversations.find(c => c.id === orderId);
            if (!conv) return;

            // Update conversation preview
            conv.last_message = {
                message: data.message,
                sender_id: data.sender_id,
                is_file: data.type === 'file',
                is_delivery_notice: data.type === 'delivery_notice',
            };

            // Increment unread count if message is not from current user
            if (data.sender_id !== currentUserId) {
                conv.unread_count = (conv.unread_count || 0) + 1;

                // Play notification sound
                document.getElementById('notification-sound')?.play().catch(() => {});

                // Update title with new total unread
                const totalUnread = conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
                window.updateUnreadTitle?.(totalUnread);
            }

            // Move conversation to top of list
            this.moveConversationToTop(orderId);
        },

        // =========================================
        // Handler: Typing indicator
        // =========================================
        handleTypingIndicator(data, userId) {
            if (data.user_id === userId) return;

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

        // =========================================
        // Handler: Read receipt
        // =========================================
        handleReadReceipt(data, userId) {
            if (data.reader_id === userId) return;

            // Update read status on messages
            data.message_ids.forEach(id => {
                const message = this.messages.find(m => m.id === id);
                if (message) {
                    message.read_at = data.read_at;
                }
            });
        },

        // =========================================
        // Action: Send message
        // =========================================
        async sendMessage() {
            const hasMessage = this.newMessage.trim();
            const hasFile = this.selectedFile;

            if ((!hasMessage && !hasFile) || !this.chatEnabled) return;

            const formData = new FormData();
            if (hasMessage) {
                formData.append('message', this.newMessage);
            }
            if (hasFile) {
                formData.append('file', this.selectedFile);
            }

            try {
                const response = await fetch(`${apiBasePath}/${this.selectedOrder}`, {
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
                        file_size: message.file_size,
                        formatted_file_size: message.formatted_file_size,
                        is_image: message.is_image,
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
                    this.clearFile();

                    // Stop typing indicator
                    this.sendTypingIndicator(false);
                }
            } catch (error) {
                console.error('Failed to send message:', error);
            }
        },

        // =========================================
        // Action: Send typing indicator
        // =========================================
        async sendTypingIndicator(isTyping) {
            if (!this.selectedOrder || !this.chatEnabled) return;

            try {
                await fetch(`${apiBasePath}/${this.selectedOrder}/typing`, {
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

        // =========================================
        // Action: Mark messages as read
        // =========================================
        async markMessagesAsRead() {
            if (!this.selectedOrder) return;

            try {
                await fetch(`${apiBasePath}/${this.selectedOrder}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                // Recalculate total unread and update title
                const conversations = this.getConversations();
                const totalUnread = conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
                window.updateUnreadTitle?.(totalUnread);
            } catch (error) {
                console.error('Failed to mark messages as read:', error);
            }
        },

        // =========================================
        // Input handler: Typing debounce
        // =========================================
        onInputChange() {
            const now = Date.now();
            // Only send "typing: true" if we haven't sent one in the last 2 seconds
            if (now - this.lastTypingSent > 2000) {
                this.sendTypingIndicator(true);
                this.lastTypingSent = now;
            }

            // Debounce the "stop typing" indicator
            clearTimeout(this.typingDebounce);
            this.typingDebounce = setTimeout(() => {
                this.sendTypingIndicator(false);
            }, 2000);
        },

        // =========================================
        // UI: Scroll to bottom
        // =========================================
        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },

        // =========================================
        // Utility: Format timestamp
        // =========================================
        formatTime(isoString) {
            const date = new Date(isoString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
                   date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
        },

        // =========================================
        // Helper: Update conversation preview in list
        // =========================================
        updateConversationPreview(orderId, message) {
            const conversations = this.getConversations();
            const conv = conversations.find(c => c.id === orderId);
            if (conv) {
                conv.last_message = {
                    message: message.message,
                    sender_id: message.sender_id,
                    is_file: message.type === 'file',
                    is_delivery_notice: message.type === 'delivery_notice',
                };
                // Clear unread count for selected conversation (user is viewing it)
                // Note: Unread increment is handled by handleOtherConversationMessage()
                if (orderId === this.selectedOrder) {
                    conv.unread_count = 0;
                }

                // Move conversation to top of list
                this.moveConversationToTop(orderId);
            }
        },

        // =========================================
        // Helper: Move conversation to top of list
        // =========================================
        moveConversationToTop(orderId) {
            const conversations = this.getConversations();
            const index = conversations.findIndex(c => c.id === orderId);
            if (index > 0) {
                const conv = conversations.splice(index, 1)[0];
                conversations.unshift(conv);
            }
        },

        // =========================================
        // Helper: Initialize online status listener
        // =========================================
        initOnlineStatusListener() {
            window.addEventListener('online-users-updated', () => {
                if (this.otherParticipantId) {
                    this.isOtherOnline = window.onlineUsers?.has(this.otherParticipantId) || false;
                }
            });
        },

        // =========================================
        // Helper: Subscribe to all conversations
        // =========================================
        subscribeToAllConversations() {
            if (!window.Echo) return;

            const conversations = this.getConversations();
            conversations.forEach(conv => {
                this.subscribeToConversation(conv.id);
            });
        },

        // =========================================
        // Helper: Initialize title with unread count
        // =========================================
        initUnreadTitle() {
            const conversations = this.getConversations();
            const totalUnread = conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
            window.updateUnreadTitle?.(totalUnread);
        },

        // =========================================
        // Helper: Load conversation data into state
        // =========================================
        loadConversationData(conversation) {
            this.messages = conversation.messages || [];
            this.chatEnabled = conversation.chat_enabled;
            this.otherParticipantName = this.getParticipantName(conversation);
            this.otherParticipantAvatar = this.getParticipantAvatar(conversation);
            this.orderNumber = conversation.order_number;
            this.orderStatus = conversation.status || conversation.status_label;
            this.otherParticipantId = this.getParticipantId(conversation);
            this.lastId = this.messages.length > 0 ? this.messages[this.messages.length - 1].id : 0;

            // Check global online status
            this.isOtherOnline = window.onlineUsers?.has(this.otherParticipantId) || false;

            this.$nextTick(() => this.scrollToBottom());
        },

        // =========================================
        // File: Select file for upload
        // =========================================
        selectFile(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.selectedFile = file;

            // Create preview URL for images
            if (this.isImageFile(file.name)) {
                this.previewUrl = URL.createObjectURL(file);
            } else {
                this.previewUrl = null;
            }
        },

        // =========================================
        // File: Clear selected file
        // =========================================
        clearFile() {
            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
            }
            this.selectedFile = null;
            this.previewUrl = null;

            // Reset file input
            const fileInput = this.$refs.fileInput;
            if (fileInput) {
                fileInput.value = '';
            }
        },

        // =========================================
        // File: Check if file is an image
        // =========================================
        isImageFile(filename) {
            if (!filename) return false;
            const ext = filename.split('.').pop().toLowerCase();
            return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext);
        },

        // =========================================
        // File: Format file size
        // =========================================
        formatFileSize(bytes) {
            if (!bytes) return '';
            const units = ['B', 'KB', 'MB', 'GB'];
            let index = 0;
            while (bytes >= 1024 && index < units.length - 1) {
                bytes /= 1024;
                index++;
            }
            return Math.round(bytes * 10) / 10 + ' ' + units[index];
        },

        // =========================================
        // Modal: Open image modal
        // =========================================
        openImageModal(url, filename, filesize, date) {
            this.modalImage = url;
            this.modalFileName = filename || '';
            this.modalFileSize = filesize || '';
            this.modalFileDate = date || '';
            this.zoomLevel = 1;
            document.body.style.overflow = 'hidden';
        },

        // =========================================
        // Modal: Close image modal
        // =========================================
        closeImageModal() {
            this.modalImage = null;
            this.modalFileName = '';
            this.modalFileSize = '';
            this.modalFileDate = '';
            this.zoomLevel = 1;
            document.body.style.overflow = '';
        },

        // =========================================
        // Modal: Zoom in
        // =========================================
        zoomIn() {
            if (this.zoomLevel < 3) {
                this.zoomLevel += 0.5;
            }
        },

        // =========================================
        // Modal: Zoom out
        // =========================================
        zoomOut() {
            if (this.zoomLevel > 0.5) {
                this.zoomLevel -= 0.5;
            }
        },

        // =========================================
        // Modal: Download file
        // =========================================
        downloadFile(url, filename) {
            const a = document.createElement('a');
            a.href = url;
            a.download = filename || 'download';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        },
    };
}
