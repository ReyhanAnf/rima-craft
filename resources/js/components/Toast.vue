<script setup>
import { useToastStore } from '@/stores/toast';
const toast = useToastStore();
</script>

<template>
    <div class="fixed top-6 right-4 md:right-6 z-[100] flex flex-col gap-3 pointer-events-none">
        <TransitionGroup name="toast">
            <div
                v-for="t in toast.toasts"
                :key="t.id"
                class="pointer-events-auto max-w-sm rounded-xl shadow-2xl backdrop-blur-xl overflow-hidden"
                :class="{
                    'bg-white dark:bg-gray-900 border border-emerald-200/50 dark:border-emerald-500/30': t.type === 'success',
                    'bg-white dark:bg-gray-900 border border-red-200/50 dark:border-red-500/30': t.type === 'error',
                    'bg-white dark:bg-gray-900 border border-amber-200/50 dark:border-amber-500/30': t.type === 'warning',
                }"
            >
                <div class="flex items-start gap-3 p-4">
                    <!-- Icon -->
                    <div
                        class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-lg"
                        :class="{
                            'bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-emerald-500/30': t.type === 'success',
                            'bg-gradient-to-br from-red-500 to-red-600 shadow-red-500/30': t.type === 'error',
                            'bg-gradient-to-br from-amber-500 to-amber-600 shadow-amber-500/30': t.type === 'warning',
                        }"
                    >
                        <svg v-if="t.type === 'success'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg v-else-if="t.type === 'error'" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <svg v-else class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">
                            <span v-if="t.type === 'success'">Berhasil!</span>
                            <span v-else-if="t.type === 'error'">Terjadi Kesalahan</span>
                            <span v-else>Peringatan</span>
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ t.message }}</p>
                    </div>

                    <button @click="toast.dismiss(t.id)" class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div
                    class="h-1 animate-pulse"
                    :class="{
                        'bg-gradient-to-r from-emerald-500 to-emerald-400': t.type === 'success',
                        'bg-gradient-to-r from-red-500 to-red-400': t.type === 'error',
                        'bg-gradient-to-r from-amber-500 to-amber-400': t.type === 'warning',
                    }"
                />
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s ease-out; }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from  { opacity: 0; transform: translateX(2rem); }
.toast-leave-to    { opacity: 0; transform: translateX(2rem); }
.toast-move        { transition: transform 0.3s; }
</style>
