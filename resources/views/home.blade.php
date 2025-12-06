<x-layouts.app>
    <x-slot:title>Home</x-slot:title>

    <div class="bg-gradient-to-br from-amber-50 to-orange-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl md:text-6xl">
                    Find Amazing Services
                </h1>
                <p class="mt-3 max-w-md mx-auto text-lg text-gray-600 sm:text-xl md:mt-5 md:max-w-3xl">
                    Connect with skilled Filipino freelancers for virtual assistance, design, writing, and more.
                </p>
                <div class="mt-8 flex justify-center gap-4">
                    <a href="{{ route('listings.index') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-8 rounded-lg transition-colors">
                        Browse Services
                    </a>
                    <a href="{{ route('become-a-worker') }}" class="bg-white hover:bg-gray-50 text-amber-600 font-semibold py-3 px-8 rounded-lg border border-amber-600 transition-colors">
                        Become a Worker
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">Featured Services</h2>
                <p class="mt-2 text-gray-600">Discover popular services from our talented workers</p>
            </div>

            @if($featuredListings->count() > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredListings as $listing)
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

                <div class="text-center mt-12">
                    <a href="{{ route('listings.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold">
                        View All Services &rarr;
                    </a>
                </div>
            @else
                <div class="text-center py-12 bg-gray-50 rounded-xl">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-600">No listings yet. Be the first to post!</p>
                    <a href="{{ route('become-a-worker') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 font-semibold">
                        Become a Worker &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">How It Works</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-amber-600">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Find a Service</h3>
                    <p class="text-gray-600">Browse our marketplace to find the perfect service for your needs.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-amber-600">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Place Your Order</h3>
                    <p class="text-gray-600">Submit your order and pay securely via GCash or bank transfer.</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-amber-600">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Get Your Work</h3>
                    <p class="text-gray-600">Receive quality work from skilled freelancers and leave a review.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
