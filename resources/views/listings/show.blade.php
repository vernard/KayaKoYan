<x-layouts.app>
    <x-slot:title>{{ $listing->title }}</x-slot:title>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="aspect-video bg-gray-100">
                            @if($listing->primaryImage)
                                <img src="{{ $listing->primaryImage->url }}" alt="{{ $listing->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        @if($listing->images->count() > 1)
                            <div class="p-4 flex gap-2 overflow-x-auto">
                                @foreach($listing->images as $image)
                                    <img src="{{ $image->url }}" alt="{{ $listing->title }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                                @endforeach
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center gap-2 mb-4">
                                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $listing->type->color() === 'primary' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $listing->type->label() }}
                                </span>
                            </div>

                            <h1 class="text-2xl font-bold text-gray-900">{{ $listing->title }}</h1>

                            <div class="mt-6 prose prose-amber max-w-none">
                                {!! $listing->description !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sticky top-8">
                        <div class="text-3xl font-bold text-amber-600 mb-6">
                            PHP {{ number_format($listing->price, 2) }}
                        </div>

                        @auth
                            @if(auth()->user()->isCustomer())
                                <form action="{{ route('customer.orders.store', $listing) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                        <textarea name="notes" id="notes" rows="3" placeholder="Add any special requirements..."
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                                    </div>
                                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        Order Now
                                    </button>
                                </form>
                            @else
                                <p class="text-gray-600 text-center">You need a customer account to order.</p>
                            @endif
                        @else
                            <a href="{{ route('register') }}" class="block w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center">
                                Sign Up to Order
                            </a>
                            <p class="mt-2 text-center text-sm text-gray-600">
                                Already have an account? <a href="{{ route('login') }}" class="text-amber-600 hover:text-amber-700">Sign in</a>
                            </p>
                        @endauth

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <a href="{{ route('worker.profile', $listing->user->workerProfile->slug ?? '#') }}" class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                                    <span class="text-amber-600 font-semibold text-lg">{{ substr($listing->user->name, 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $listing->user->name }}</p>
                                    <p class="text-sm text-gray-500">View Profile &rarr;</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if($relatedListings->count() > 0)
                <div class="mt-12">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">More from this Worker</h2>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach($relatedListings as $related)
                            <a href="{{ route('listings.show', $related->slug) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                                <div class="aspect-video bg-gray-100">
                                    @if($related->primaryImage)
                                        <img src="{{ $related->primaryImage->url }}" alt="{{ $related->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $related->title }}</h3>
                                    <p class="text-lg font-bold text-amber-600 mt-2">PHP {{ number_format($related->price, 2) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
