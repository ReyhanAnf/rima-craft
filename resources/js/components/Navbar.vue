<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { useCartStore } from '@/stores/cart';
import { useThemeStore } from '@/stores/theme';

const props = defineProps({
    businessName: { type: String, default: 'Rima Craft' },
});

const emit = defineEmits(['open-cart']);

const page = usePage();
const authUser = computed(() => page.props.auth?.user);

const dashboardRouteName = computed(() => {
    if (!page.props.auth?.roles) return null;
    const roles = page.props.auth.roles;
    if (roles.includes('customer')) return 'customer.dashboard';
    if (roles.includes('reseller')) return 'reseller.dashboard';
    if (roles.some(r => ['super-admin', 'owner', 'operator'].includes(r))) return 'dashboard';
    return null;
});

const logout = () => {
    router.post(route('logout'));
};

const cart = useCartStore();
const theme = useThemeStore();
const scrolled = ref(false);

const siteConfig = computed(() => page.props.siteConfig || {});

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
                <Link href="/" class="flex items-center gap-3">
                    <img v-if="siteConfig.logo_url" :src="`/storage/${siteConfig.logo_url}`" class="h-10 w-auto object-contain rounded" alt="Logo" />
                    <div>
                        <h1 class="text-xl md:text-2xl font-serif font-bold tracking-[0.10em] text-gray-900 dark:text-white uppercase transition-colors leading-none">
                            {{ businessName }}
                        </h1>
                        <p v-if="siteConfig.business_subtitle" class="text-[9px] md:text-[10px] text-gray-500 dark:text-gray-400 font-medium tracking-normal mt-1.5 leading-tight">
                            {{ siteConfig.business_subtitle }}
                        </p>
                    </div>
                </Link>
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


                <!-- Portal Saya (if logged in - Desktop) -->
                <Link v-if="authUser && dashboardRouteName" :href="route(dashboardRouteName)"
                    class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800"
                    title="Portal Dashboard Saya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Portal Saya
                </Link>

                <!-- Masuk Hover Dropdown (if guest - Desktop) -->
                <div v-if="!authUser" class="relative group hidden sm:block">
                    <button class="flex items-center gap-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Masuk
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <!-- Dropdown Panel -->
                    <div class="absolute right-0 mt-1 w-48 rounded-xl bg-white dark:bg-gray-900 border border-gray-150 dark:border-gray-800 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 py-1.5">
                        <a :href="route('customer.login')" class="block px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            Masuk Customer
                        </a>
                        <a :href="route('reseller.login')" class="block px-4 py-2.5 text-xs font-semibold text-gray-750 dark:text-gray-350 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-amber-600 dark:hover:text-amber-400 transition-colors border-t border-gray-100 dark:border-gray-800">
                            Masuk Reseller
                        </a>
                    </div>
                </div>

                <!-- Keluar (if logged in - Desktop) -->
                <button v-if="authUser" @click="logout"
                    class="hidden sm:flex items-center gap-1.5 text-xs font-semibold text-red-650 hover:text-red-700 dark:text-red-400 dark:hover:text-red-350 transition-colors px-2 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>

                <!-- Mobile Auth Buttons -->
                <!-- Portal Saya Icon (if logged in - Mobile) -->
                <Link v-if="authUser && dashboardRouteName" :href="route(dashboardRouteName)"
                    class="flex sm:hidden items-center justify-center p-2 text-gray-700 dark:text-gray-300 hover:text-amber-500 transition-colors"
                    title="Portal Saya">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </Link>

                <!-- Logout Icon (if logged in - Mobile) -->
                <button v-if="authUser" @click="logout"
                    class="flex sm:hidden items-center justify-center p-2 text-red-500 hover:text-red-600 transition-colors"
                    title="Keluar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>

                <!-- Login Icon (if guest - Mobile) -->
                <a v-if="!authUser" :href="route('customer.login')"
                    class="flex sm:hidden items-center justify-center p-2 text-gray-700 dark:text-gray-300 hover:text-amber-500 transition-colors"
                    title="Masuk">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </a>

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
