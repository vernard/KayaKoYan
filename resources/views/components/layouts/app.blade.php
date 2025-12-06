<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Kaya Ko Yan' }} - Marketplace</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

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
                            <a href="{{ route('customer.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('customer.orders.index') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                My Orders
                            </a>
                        @elseif(auth()->user()->isWorker())
                            <a href="{{ route('filament.worker.pages.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Worker Dashboard
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Admin Panel
                            </a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                Logout
                            </button>
                        </form>
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
</body>
</html>
