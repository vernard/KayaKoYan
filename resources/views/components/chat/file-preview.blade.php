@props(['darkMode' => false])

@php
$containerDark = $darkMode ? 'dark:bg-gray-800 dark:border-gray-700' : '';
$iconBgDark = $darkMode ? 'dark:bg-gray-700' : '';
$textDark = $darkMode ? 'dark:text-white' : '';
$subtextDark = $darkMode ? 'dark:text-gray-400' : '';
@endphp

{{-- File Preview (before sending) --}}
<div x-show="selectedFile" x-transition class="mb-3 p-3 bg-gray-50 {{ $containerDark }} rounded-lg border border-gray-200">
    <div class="flex items-center gap-3">
        <template x-if="previewUrl">
            <img :src="previewUrl" class="w-16 h-16 object-cover rounded-lg">
        </template>
        <template x-if="!previewUrl && selectedFile">
            <div class="w-16 h-16 bg-gray-200 {{ $iconBgDark }} rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </template>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 {{ $textDark }} truncate" x-text="selectedFile?.name"></p>
            <p class="text-xs text-gray-500 {{ $subtextDark }}" x-text="selectedFile ? formatFileSize(selectedFile.size) : ''"></p>
        </div>
        <button type="button" @click="clearFile()" class="p-1 text-gray-400 hover:text-red-500">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
