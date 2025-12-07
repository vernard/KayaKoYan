{{-- Image Modal - Must be inside x-data scope, teleported to body --}}
<template x-teleport="body">
    <div x-show="modalImage" x-transition.opacity class="fixed inset-0 z-[9999] bg-black/90 flex flex-col" @keydown.escape.window="closeImageModal()" @click.self="closeImageModal()">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between p-4 text-white">
            <div class="flex items-center gap-4">
                <button @click="closeImageModal()" class="p-2 hover:bg-white/10 rounded-lg transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div class="text-sm">
                    <span class="font-medium" x-text="modalFileName"></span>
                    <template x-if="modalFileSize || modalFileDate">
                        <span class="text-white/60 ml-2">
                            <template x-if="modalFileSize"><span x-text="modalFileSize"></span></template>
                            <template x-if="modalFileSize && modalFileDate"><span> &bull; </span></template>
                            <template x-if="modalFileDate"><span x-text="modalFileDate"></span></template>
                        </span>
                    </template>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button @click="zoomOut()" class="p-2 hover:bg-white/10 rounded-lg transition-colors" :disabled="zoomLevel <= 0.5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                    </svg>
                </button>
                <span class="text-sm min-w-[3rem] text-center" x-text="Math.round(zoomLevel * 100) + '%'"></span>
                <button @click="zoomIn()" class="p-2 hover:bg-white/10 rounded-lg transition-colors" :disabled="zoomLevel >= 3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                    </svg>
                </button>
                <button @click="downloadFile(modalImage, modalFileName)" class="p-2 hover:bg-white/10 rounded-lg transition-colors ml-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Modal Image Container --}}
        <div class="flex-1 overflow-auto flex items-center justify-center p-4">
            <img :src="modalImage" :alt="modalFileName" class="max-w-none transition-transform duration-200" :style="{ transform: 'scale(' + zoomLevel + ')' }">
        </div>
    </div>
</template>
