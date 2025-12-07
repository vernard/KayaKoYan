@props(['placeholder' => 'Type your message...', 'darkMode' => false])

@php
$hoverDark = $darkMode ? 'dark:hover:bg-gray-800' : '';
$inputDark = $darkMode ? 'dark:border-gray-600 dark:bg-gray-800 dark:text-white' : '';
$disabledDark = $darkMode ? 'dark:disabled:bg-gray-700' : '';
@endphp

<x-chat.file-preview :dark-mode="$darkMode" />

<form @submit.prevent="sendMessage()" class="flex gap-2">
    <input type="file" x-ref="fileInput" @change="selectFile($event)" class="hidden" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
    <button type="button" @click="$refs.fileInput.click()" class="p-3 text-gray-500 hover:text-amber-600 hover:bg-gray-100 {{ $hoverDark }} rounded-lg transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
    </button>
    <input type="text" x-model="newMessage" @input="onInputChange()" :placeholder="selectedFile ? 'Add a message (optional)...' : '{{ $placeholder }}'"
           class="flex-1 px-4 py-3 border border-gray-300 {{ $inputDark }} rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
    <button type="submit" :disabled="!newMessage.trim() && !selectedFile"
            class="bg-amber-600 hover:bg-amber-700 disabled:bg-gray-300 {{ $disabledDark }} text-white font-semibold py-3 px-6 rounded-lg transition-colors">
        Send
    </button>
</form>
