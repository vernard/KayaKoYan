<x-layouts.app>
    <x-slot:title>Chat - {{ $order->order_number }}</x-slot:title>

    <div class="h-[calc(100vh-4rem)] flex flex-col">
        <div class="bg-white border-b border-gray-200 p-4">
            <div class="max-w-4xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ auth()->user()->isCustomer() ? route('customer.orders.show', $order) : route('filament.worker.resources.orders.view', $order) }}" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="font-semibold text-gray-900">{{ $order->order_number }}</h1>
                        <p class="text-sm text-gray-500">
                            {{ auth()->user()->isCustomer() ? $order->worker->name : $order->customer->name }}
                        </p>
                    </div>
                </div>
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
            </div>
        </div>

        <div class="flex-1 overflow-hidden" x-data="chatComponent()" x-init="init()">
            <div class="max-w-4xl mx-auto h-full flex flex-col">
                <div x-ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    @foreach($order->chatMessages as $message)
                        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[70%] {{ $message->sender_id === auth()->id() ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-900' }} rounded-lg p-4">
                                @if($message->isDeliveryNotice())
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span class="font-semibold">Delivery Notice</span>
                                    </div>
                                @endif
                                @if($message->isFile())
                                    <a href="{{ $message->file_url }}" target="_blank" class="flex items-center gap-2 underline">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $message->file_name }}
                                    </a>
                                @endif
                                @if($message->message)
                                    <p>{{ $message->message }}</p>
                                @endif
                                <p class="text-xs {{ $message->sender_id === auth()->id() ? 'text-amber-200' : 'text-gray-500' }} mt-2">
                                    {{ $message->sender->name }} &bull; {{ $message->created_at->format('M d, g:i A') }}
                                </p>
                            </div>
                        </div>
                    @endforeach

                    <template x-for="message in newMessages" :key="message.id">
                        <div :class="message.is_own ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="message.is_own ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-900'" class="max-w-[70%] rounded-lg p-4">
                                <template x-if="message.type === 'delivery_notice'">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                        </svg>
                                        <span class="font-semibold">Delivery Notice</span>
                                    </div>
                                </template>
                                <template x-if="message.type === 'file'">
                                    <a :href="message.file_url" target="_blank" class="flex items-center gap-2 underline">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="message.file_name"></span>
                                    </a>
                                </template>
                                <p x-text="message.message" x-show="message.message"></p>
                                <p :class="message.is_own ? 'text-amber-200' : 'text-gray-500'" class="text-xs mt-2">
                                    <span x-text="message.sender_name"></span> &bull; <span x-text="formatTime(message.created_at)"></span>
                                </p>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-200 p-4 bg-white">
                    <form @submit.prevent="sendMessage()" class="flex gap-4">
                        <input type="text" x-model="newMessage" placeholder="Type your message..."
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <button type="submit" :disabled="!newMessage.trim() && !file" class="bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 text-white font-semibold py-3 px-6 rounded-lg transition-colors">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function chatComponent() {
            return {
                newMessages: [],
                newMessage: '',
                file: null,
                eventSource: null,
                lastId: {{ $order->chatMessages->last()?->id ?? 0 }},

                init() {
                    this.scrollToBottom();
                    this.connect();
                },

                connect() {
                    const url = '{{ auth()->user()->isCustomer() ? route('customer.chat.stream', $order) : route('worker.chat.stream', $order) }}?last_id=' + this.lastId;
                    this.eventSource = new EventSource(url);

                    this.eventSource.onmessage = (event) => {
                        const message = JSON.parse(event.data);
                        if (!this.newMessages.find(m => m.id === message.id)) {
                            this.newMessages.push(message);
                            this.lastId = message.id;
                            this.$nextTick(() => this.scrollToBottom());
                        }
                    };

                    this.eventSource.onerror = () => {
                        this.eventSource.close();
                        setTimeout(() => this.connect(), 5000);
                    };
                },

                async sendMessage() {
                    if (!this.newMessage.trim()) return;

                    const formData = new FormData();
                    formData.append('message', this.newMessage);

                    try {
                        const response = await fetch('{{ auth()->user()->isCustomer() ? route('customer.chat.store', $order) : route('worker.chat.store', $order) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        if (response.ok) {
                            const data = await response.json();
                            const message = data.message;

                            // Add message to display immediately
                            if (!this.newMessages.find(m => m.id === message.id)) {
                                this.newMessages.push({
                                    id: message.id,
                                    sender_id: message.sender_id,
                                    sender_name: message.sender.name,
                                    message: message.message,
                                    type: message.type,
                                    file_path: message.file_path,
                                    file_name: message.file_name,
                                    file_url: message.file_url,
                                    created_at: message.created_at,
                                    is_own: true,
                                });
                                this.lastId = message.id;
                                this.$nextTick(() => this.scrollToBottom());
                            }

                            this.newMessage = '';
                        }
                    } catch (error) {
                        console.error('Failed to send message:', error);
                    }
                },

                scrollToBottom() {
                    const container = this.$refs.messagesContainer;
                    container.scrollTop = container.scrollHeight;
                },

                formatTime(isoString) {
                    const date = new Date(isoString);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ', ' +
                           date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
                }
            }
        }
    </script>
</x-layouts.app>
