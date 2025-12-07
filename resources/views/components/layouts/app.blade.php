<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Kaya Ko Yan' }} - Marketplace</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @auth
    <script>window.userId = {{ auth()->id() }};</script>
    @endauth
    @guest
    <script>window.userId = null;</script>
    @endguest
    <script>
        window.REVERB_APP_KEY = '{{ config('reverb.apps.apps.0.key') }}';
        window.REVERB_HOST = '{{ config('reverb.apps.apps.0.options.host') }}';
        window.REVERB_PORT = '{{ config('reverb.apps.apps.0.options.port') }}';
        window.REVERB_SCHEME = '{{ config('reverb.apps.apps.0.options.scheme') }}';
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <span class="text-xl font-bold text-amber-600">Kaya Ko Yan</span>
                    </a>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="{{ route('listings.index') }}" class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                            Browse Listings
                        </a>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            Sign Up
                        </a>
                    @else
                        @if(auth()->user()->isCustomer())
                            <a href="{{ route('chats.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium" x-data="chatBadge()" x-init="init()">
                                <span class="flex items-center gap-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Chat
                                    <span x-show="unreadCount > 0" x-text="'(' + unreadCount + ')'" class="text-amber-600"></span>
                                </span>
                            </a>

                            <!-- User Avatar Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" class="flex items-center focus:outline-none">
                                    <img src="{{ auth()->user()->avatar_url_small }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover border-2 border-transparent hover:border-amber-500 transition-colors">
                                </button>

                                <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    </div>
                                    <a href="{{ route('customer.orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Orders</a>
                                    <a href="{{ route('customer.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Account</a>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                                    </form>
                                </div>
                            </div>
                        @elseif(auth()->user()->isWorker())
                            <a href="{{ route('filament.worker.pages.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Worker Dashboard
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Logout</button>
                            </form>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Admin Panel
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-gray-600 hover:text-gray-900 text-sm font-medium">Logout</button>
                            </form>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Kaya Ko Yan. All rights reserved.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="{{ route('become-a-worker') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                        Become a Worker
                    </a>
                </div>
            </div>
        </div>
    </footer>
    <audio id="notification-sound" src="/sounds/notification.mp3" preload="auto"></audio>
    @auth
        @if(auth()->user()->isCustomer())
        <script>
            function chatBadge() {
                return {
                    unreadCount: 0,
                    init() {
                        this.fetchUnreadCount();
                        // Subscribe to real-time notifications
                        if (window.Echo) {
                            window.Echo.private(`user.{{ auth()->id() }}.notifications`)
                                .listen('.unread.updated', (data) => {
                                    // Play notification sound only if not on chat page
                                    if (!window.location.pathname.startsWith('/chats')) {
                                        document.getElementById('notification-sound')?.play().catch(() => {});
                                    }
                                    this.unreadCount = data.count;
                                    window.updateUnreadTitle?.(data.count);
                                });
                        }
                    },
                    async fetchUnreadCount() {
                        try {
                            const response = await fetch('{{ route("chats.unread") }}');
                            const data = await response.json();
                            this.unreadCount = data.count;
                            window.updateUnreadTitle?.(data.count);
                        } catch (error) {
                            console.error('Failed to fetch unread count:', error);
                        }
                    }
                }
            }
        </script>
        @endif
    @endauth
</body>
</html>
