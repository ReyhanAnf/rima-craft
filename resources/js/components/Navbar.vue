<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useThemeStore } from '@/stores/theme';

const props = defineProps({
    businessName: { type: String, default: 'Rima Craft' },
});

const emit = defineEmits(['open-cart']);

const cart = useCartStore();
const theme = useThemeStore();
const scrolled = ref(false);

function onScroll() {
    scrolled.value = window.pageYOffset > 20;
}

onMounted(() => window.addEventListener('scroll', onScroll));
onUnmounted(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <header
        :class="scrolled
            ? 'bg-white/95 dark:bg-[#0a0a0a]/95 backdrop-blur-md border-b border-gray-200 dark:border-[#1a1a1a] shadow-lg'
            : 'bg-transparent border-b border-transparent'"
        class="fixed top-0 w-full z-40 transition-all duration-500"
    >
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-all duration-500"
            :class="scrolled ? 'h-16' : 'h-24'"
        >
            <div class="flex items-center">
                <h1 class="text-2xl md:text-3xl font-serif font-bold tracking-[0.15em] text-gray-900 dark:text-white uppercase transition-colors">
                    {{ businessName }}
                </h1>
            </div>

            <div class="flex items-center gap-2 md:gap-4">
                <!-- Dark mode toggle -->
                <button
                    @click="theme.toggle()"
                    class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                >
                    <svg v-if="!theme.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <!-- Cart button -->
                <button
                    @click="$emit('open-cart')"
                    class="relative flex items-center justify-center p-2 text-gray-900 dark:text-white hover:text-amber-500 transition-colors group"
                >
                    <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <Transition name="bounce">
                        <span
                            v-if="cart.totalItems > 0"
                            class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold text-black bg-amber-500 rounded-full shadow-sm"
                        >
                            {{ cart.totalItems }}
                        </span>
                    </Transition>
                </button>
            </div>
        </div>
    </header>
</template>
