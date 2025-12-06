<x-layouts.app>
    <x-slot:title>{{ $profile->user->name }}</x-slot:title>

    <div class="bg-gradient-to-br from-amber-50 to-orange-100 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8">
                <div class="w-32 h-32 bg-amber-200 rounded-full flex items-center justify-center flex-shrink-0">
                    @if($profile->avatar_path)
                        <img src="{{ asset('storage/' . $profile->avatar_path) }}" alt="{{ $profile->user->name }}" class="w-full h-full object-cover rounded-full">
                    @else
                        <span class="text-amber-700 font-bold text-4xl">{{ substr($profile->user->name, 0, 1) }}</span>
                    @endif
                </div>
                <div class="text-center md:text-left">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $profile->user->name }}</h1>
                    @if($profile->location)
                        <p class="text-gray-600 mt-1 flex items-center justify-center md:justify-start gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $profile->location }}
                        </p>
                    @endif
                    @if($profile->bio)
                        <p class="text-gray-700 mt-4 max-w-2xl">{{ $profile->bio }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-8">Services by {{ $profile->user->name }}</h2>

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
                                <p class="text-lg font-bold text-amber-600 mt-2">PHP {{ number_format($listing->price, 2) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-xl">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <p class="text-gray-600">No listings yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
