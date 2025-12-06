<x-layouts.app>
    <x-slot:title>Order {{ $order->order_number }}</x-slot:title>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <a href="{{ route('customer.orders.index') }}" class="text-amber-600 hover:text-amber-700 font-medium">
                    &larr; Back to Orders
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                                <p class="text-gray-500">Placed on {{ $order->created_at->format('F d, Y') }}</p>
                            </div>
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full
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
                        </div>

                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                            <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                @if($order->listing->primaryImage)
                                    <img src="{{ $order->listing->primaryImage->url }}" alt="{{ $order->listing->title }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $order->listing->title }}</h3>
                                <p class="text-sm text-gray-500">{{ $order->listing->type->label() }}</p>
                            </div>
                            <p class="font-bold text-amber-600">PHP {{ number_format($order->total_price, 2) }}</p>
                        </div>

                        @if($order->notes)
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-700">Your Notes:</p>
                                <p class="text-gray-600">{{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($order->delivery)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Delivery</h2>
                            <p class="text-gray-700">{{ $order->delivery->notes }}</p>

                            @if($order->delivery->files->count() > 0)
                                <div class="mt-4 space-y-2">
                                    <p class="text-sm font-medium text-gray-700">Attached Files:</p>
                                    @foreach($order->delivery->files as $file)
                                        <a href="{{ $file->url }}" target="_blank" class="flex items-center gap-2 p-2 bg-gray-50 rounded hover:bg-gray-100">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-sm text-gray-600">{{ $file->file_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if($order->status === \App\Enums\OrderStatus::Delivered)
                                <form action="{{ route('customer.orders.accept', $order) }}" method="POST" class="mt-6">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        Accept Delivery & Complete Order
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif

                    @if($order->canDownloadDigitalProduct())
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Digital Product</h2>
                            <p class="text-gray-600 mb-4">Your digital product is ready for download.</p>
                            <a href="{{ route('customer.orders.download', $order) }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Now
                            </a>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Worker</h2>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                                <span class="text-amber-600 font-semibold">{{ substr($order->worker->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $order->worker->name }}</p>
                                <a href="{{ route('worker.profile', $order->worker->workerProfile->slug ?? '#') }}" class="text-sm text-amber-600 hover:text-amber-700">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($order->status === \App\Enums\OrderStatus::PendingPayment)
                        <div class="bg-amber-50 rounded-xl border border-amber-200 p-6">
                            <h2 class="text-lg font-semibold text-amber-900 mb-2">Payment Required</h2>
                            <p class="text-amber-800 text-sm mb-4">Please submit payment to proceed with your order.</p>
                            <a href="{{ route('customer.payment.create', $order) }}" class="block w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition-colors">
                                Submit Payment
                            </a>
                        </div>
                    @endif

                    @if($order->latestPayment)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm text-gray-500">Method</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->latestPayment->method->label() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Reference #</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->latestPayment->reference_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Status</dt>
                                    <dd>
                                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                            @switch($order->latestPayment->status->color())
                                                @case('warning') bg-yellow-100 text-yellow-700 @break
                                                @case('success') bg-green-100 text-green-700 @break
                                                @case('danger') bg-red-100 text-red-700 @break
                                            @endswitch
                                        ">
                                            {{ $order->latestPayment->status->label() }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    <a href="{{ route('chats.show', $order) }}" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Chat with Worker</p>
                                <p class="text-sm text-gray-500">Send messages in real-time</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
