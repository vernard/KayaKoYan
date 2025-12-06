<x-layouts.app>
    <x-slot:title>Browse Services</x-slot:title>

    <div class="bg-white py-8 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">Browse Services</h1>
            <p class="mt-2 text-gray-600">Find the perfect service for your needs</p>

            <form method="GET" action="{{ route('listings.index') }}" class="mt-6 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
                <select name="type" class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">All Types</option>
                    <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Services</option>
                    <option value="digital_product" {{ request('type') === 'digital_product' ? 'selected' : '' }}>Digital Products</option>
                </select>
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                    Search
                </button>
            </form>
        </div>
    </div>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($listings->count() > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($listings as $listing)
                        <a href="{{ route('listings.show', $listing->slug) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                            <div class="aspect-video bg-gray-100 relative">
                                @if($listing->primaryImage)
                                    <img src="{{ $listing->primaryImage->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute top-2 right-2 px-2 py-1 text-xs font-medium rounded-full {{ $listing->type->color() === 'primary' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $listing->type->label() }}
                                </span>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $listing->title }}</h3>
                                <p class="text-sm text-gray-500 mt-1">by {{ $listing->user->name }}</p>
                                <p class="text-lg font-bold text-amber-600 mt-2">PHP {{ number_format($listing->price, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $listings->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-xl">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-600">No listings found matching your criteria.</p>
                    <a href="{{ route('listings.index') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 font-semibold">
                        Clear filters &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
