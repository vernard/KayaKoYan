import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { createChatComponent } from './chat';

// Export chat component globally for use in blade templates
window.createChatComponent = createChatComponent;

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Make Pusher available globally (required by Echo for Reverb)
window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.REVERB_APP_KEY,
    wsHost: window.REVERB_HOST,
    wsPort: window.REVERB_PORT ?? 80,
    wssPort: window.REVERB_PORT ?? 443,
    forceTLS: (window.REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Global online users tracking
window.onlineUsers = new Set();
window.offlineTimeouts = new Map(); // Track pending offline transitions

// Join global presence channel if user is logged in
if (window.userId) {
    window.Echo.join('users.online')
        .here(users => {
            users.forEach(u => {
                // Clear any pending offline timeout
                if (window.offlineTimeouts.has(u.id)) {
                    clearTimeout(window.offlineTimeouts.get(u.id));
                    window.offlineTimeouts.delete(u.id);
                }
                window.onlineUsers.add(u.id);
            });
            window.dispatchEvent(new CustomEvent('online-users-updated'));
        })
        .joining(user => {
            // Clear any pending offline timeout for this user
            if (window.offlineTimeouts.has(user.id)) {
                clearTimeout(window.offlineTimeouts.get(user.id));
                window.offlineTimeouts.delete(user.id);
            }
            window.onlineUsers.add(user.id);
            window.dispatchEvent(new CustomEvent('online-users-updated'));
        })
        .leaving(user => {
            // Delay marking user as offline by 2 seconds to handle page navigation
            const timeoutId = setTimeout(() => {
                window.onlineUsers.delete(user.id);
                window.offlineTimeouts.delete(user.id);
                window.dispatchEvent(new CustomEvent('online-users-updated'));
            }, 2000);
            window.offlineTimeouts.set(user.id, timeoutId);
        });
}

// Title flash for unread messages
window.originalTitle = document.title;
window.totalUnread = 0;
window.titleFlashInterval = null;

window.updateUnreadTitle = function(count) {
    window.totalUnread = count;

    if (count > 0 && !window.titleFlashInterval) {
        let showUnread = true;
        window.titleFlashInterval = setInterval(() => {
            document.title = showUnread
                ? `(${count}) Unread Chats - Kaya Ko Yan`
                : window.originalTitle;
            showUnread = !showUnread;
        }, 1000);
    } else if (count === 0 && window.titleFlashInterval) {
        clearInterval(window.titleFlashInterval);
        window.titleFlashInterval = null;
        document.title = window.originalTitle;
    } else if (count > 0 && window.titleFlashInterval) {
        // Update the count in the existing interval
        clearInterval(window.titleFlashInterval);
        let showUnread = true;
        window.titleFlashInterval = setInterval(() => {
            document.title = showUnread
                ? `(${count}) Unread Chats - Kaya Ko Yan`
                : window.originalTitle;
            showUnread = !showUnread;
        }, 1000);
    }
};
