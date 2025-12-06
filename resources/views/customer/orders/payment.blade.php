<x-layouts.app>
    <x-slot:title>Submit Payment</x-slot:title>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <a href="{{ route('customer.orders.show', $order) }}" class="text-amber-600 hover:text-amber-700 font-medium">
                    &larr; Back to Order
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Submit Payment</h1>
                <p class="text-gray-600 mb-8">Order: {{ $order->order_number }}</p>

                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-8">
                    <p class="font-semibold text-amber-900">Amount Due: PHP {{ number_format($order->total_price, 2) }}</p>
                </div>

                @if($order->worker->workerProfile)
                    <div class="mb-8 space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Payment Options</h2>

                        @if($order->worker->workerProfile->hasGcash())
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <h3 class="font-semibold text-gray-900">GCash</h3>
                                <p class="text-gray-600">{{ $order->worker->workerProfile->gcash_number }}</p>
                                <p class="text-gray-600">{{ $order->worker->workerProfile->gcash_name }}</p>
                            </div>
                        @endif

                        @if($order->worker->workerProfile->hasBank())
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <h3 class="font-semibold text-gray-900">Bank Transfer</h3>
                                <p class="text-gray-600">{{ $order->worker->workerProfile->bank_name }}</p>
                                <p class="text-gray-600">Account #: {{ $order->worker->workerProfile->bank_account_number }}</p>
                                <p class="text-gray-600">Name: {{ $order->worker->workerProfile->bank_account_name }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('customer.payment.store', $order) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label for="method" class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                        <select name="method" id="method" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('method') border-red-500 @enderror">
                            <option value="">Select method...</option>
                            <option value="gcash" {{ old('method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                            <option value="bank_transfer" {{ old('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                        @error('method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                        <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('reference_number') border-red-500 @enderror"
                            placeholder="Enter your transaction reference number">
                        @error('reference_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="proof" class="block text-sm font-medium text-gray-700 mb-1">Payment Proof (Screenshot)</label>
                        <input type="file" id="proof" name="proof" required accept="image/*"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('proof') border-red-500 @enderror">
                        <p class="mt-1 text-sm text-gray-500">Upload a screenshot of your payment confirmation.</p>
                        @error('proof')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 @error('notes') border-red-500 @enderror"
                            placeholder="Any additional notes about your payment...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                        Submit Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
