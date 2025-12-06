<x-layouts.app>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Welcome back, {{ auth()->user()->name }}!</h1>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $activeOrders }}</p>
                            <p class="text-gray-600 text-sm">Active Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $completedOrders }}</p>
                            <p class="text-gray-600 text-sm">Completed Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <a href="{{ route('listings.index') }}" class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">Browse Services</p>
                            <p class="text-gray-600 text-sm">Find new services &rarr;</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Recent Orders</h2>
                        <a href="{{ route('customer.orders.index') }}" class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                            View All &rarr;
                        </a>
                    </div>
                </div>

                @if($recentOrders->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($recentOrders as $order)
                            <a href="{{ route('customer.orders.show', $order) }}" class="flex items-center gap-4 p-6 hover:bg-gray-50 transition-colors">
                                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                    @if($order->listing->primaryImage)
                                        <img src="{{ $order->listing->primaryImage->url }}" alt="{{ $order->listing->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-900 truncate">{{ $order->listing->title }}</p>
                                    <p class="text-sm text-gray-500">{{ $order->order_number }} &bull; {{ $order->worker->name }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                        @switch($order->status->color())
                                            @case('gray') bg-gray-100 text-gray-700 @break
                                            @case('warning') bg-yellow-100 text-yellow-700 @break
                                            @case('info') bg-blue-100 text-blue-700 @break
                                            @case('primary') bg-amber-100 text-amber-700 @break
                                            @case('success') bg-green-100 text-green-700 @break
                                            @case('danger') bg-red-100 text-red-700 @break
                                        @endswitch
                                    ">
                                        {{ $order->status->label() }}
                                    </span>
                                    <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <p class="text-gray-600 mb-4">You haven't placed any orders yet.</p>
                        <a href="{{ route('listings.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold">
                            Browse Services &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
