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

    // Update Filament sidebar badge (worker panel)
    const navLink = document.querySelector('a[href$="/worker/chat"]');
    if (navLink) {
        let badgeContainer = navLink.querySelector('.fi-sidebar-item-badge-ctn');

        if (count > 0) {
            if (!badgeContainer) {
                // Create badge structure matching Filament's markup
                badgeContainer = document.createElement('span');
                badgeContainer.className = 'fi-sidebar-item-badge-ctn';

                const badge = document.createElement('span');
                badge.className = 'fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 min-w-[theme(spacing.5)] py-0.5 fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30 fi-color-danger';
                badge.style.setProperty('--c-50', 'var(--danger-50)');
                badge.style.setProperty('--c-400', 'var(--danger-400)');
                badge.style.setProperty('--c-600', 'var(--danger-600)');

                const labelCtn = document.createElement('span');
                labelCtn.className = 'fi-badge-label-ctn';
                const label = document.createElement('span');
                label.className = 'fi-badge-label';
                labelCtn.appendChild(label);
                badge.appendChild(labelCtn);
                badgeContainer.appendChild(badge);
                navLink.appendChild(badgeContainer);
            }

            const label = badgeContainer.querySelector('.fi-badge-label');
            if (label) label.textContent = count;
            badgeContainer.style.display = '';
        } else if (badgeContainer) {
            badgeContainer.style.display = 'none';
        }
    }

    // Title flash logic
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

// Set up notification listener for worker panel (non-chat pages)
// The chat page sets up its own listener in the Alpine component
if (window.userId && window.location.pathname.startsWith('/worker') && !window.location.pathname.endsWith('/worker/chat')) {
    window.Echo.private(`user.${window.userId}.notifications`)
        .listen('.unread.updated', (data) => {
            // Play notification sound
            const audio = document.getElementById('notification-sound');
            if (audio) audio.play().catch(() => {});

            // Update title and badge
            window.updateUnreadTitle(data.count);
        });
}
