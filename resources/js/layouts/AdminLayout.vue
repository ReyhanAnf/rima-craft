<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import { useThemeStore } from '@/stores/theme';
import { useAdminStore } from '@/stores/admin';

const page = usePage();
const user = page.props.auth?.user || {};
const siteConfig = page.props.siteConfig || {};
const menuCategories = page.props.menus || [];

const themeStore = useThemeStore();
const adminStore = useAdminStore();

const isMobileMenuOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const permissions = computed(() => page.props.auth?.permissions || []);
const roles = computed(() => page.props.auth?.roles || []);

const hasPermission = (perm) => {
    return permissions.value.includes(perm);
};

// Filtered navigation structure grouped by category
const categorizedNavigation = computed(() => {
    const list = [];
    const isCustomer = roles.value.includes('customer');
    const isPartner = roles.value.includes('partner');

    // Customer Portal Items
    if (isCustomer) {
        return [
            {
                title: 'Portal Pelanggan',
                items: [
                    { name: 'Portal Dashboard', route: 'customer.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6' },
                    { name: 'Pesanan Saya', route: 'customer.orders', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2' },
                    { name: 'Profil Saya', route: 'customer.profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }
                ]
            }
        ];
    }

    // Partner Reseller Portal Items
    if (isPartner) {
        return [
            {
                title: 'Portal Reseller',
                items: [
                    { name: 'Portal Reseller', route: 'partner.dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3M9 21h6' },
                    { name: 'Riwayat Order', route: 'partner.orders', icon: 'M9 5H7a2 2 0 00-2-2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2' },
                    { name: 'Tagihan / Billing', route: 'partner.billing', icon: 'M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                    { name: 'Profil Reseller', route: 'partner.profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' }
                ]
            }
        ];
    }

    // Admin Config-driven Categorized Items
    for (const cat of menuCategories) {
        const filteredItems = cat.items.filter(item => !item.permission || hasPermission(item.permission));
        if (filteredItems.length > 0) {
            list.push({
                title: cat.title,
                items: filteredItems
            });
        }
    }

    return list;
});

// Flatten navigation helper for mobile/active check searches
const flatNavigation = computed(() => {
    const list = [];
    categorizedNavigation.value.forEach(cat => {
        cat.items.forEach(item => {
            list.push(item);
        });
    });
    return list;
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 flex transition-colors duration-300">
        <Toast />

        <aside
            :class="[
                'bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-all duration-300 flex-col shrink-0 hidden lg:flex h-screen sticky top-0 overflow-y-auto',
                adminStore.isSidebarOpen ? 'w-64' : 'w-20'
            ]"
        >
            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-gray-800">
                <Link href="/dashboard" class="flex items-center gap-2 overflow-hidden">
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold">R</span>
                    </div>
                    <span v-show="adminStore.isSidebarOpen" class="font-bold text-lg text-gray-900 dark:text-white transition-opacity duration-200">
                        {{ siteConfig.business_name || 'Rima Craft' }}
                    </span>
                </Link>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 py-4 px-3 space-y-4 overflow-y-auto">
                <div v-for="category in categorizedNavigation" :key="category.title" class="space-y-1">
                    <!-- Category Title -->
                    <span
                        v-show="adminStore.isSidebarOpen"
                        class="px-3 text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1.5"
                    >
                        {{ category.title }}
                    </span>
                    <hr v-show="!adminStore.isSidebarOpen" class="border-gray-100 dark:border-gray-800 my-2" />

                    <!-- Category Items -->
                    <Link
                        v-for="item in category.items"
                        :key="item.name"
                        :href="route(item.route)"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-all group',
                            route().current(item.route)
                                ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/10'
                                : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white'
                        ]"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        <span v-show="adminStore.isSidebarOpen" class="truncate">{{ item.name }}</span>
                    </Link>
                </div>
            </nav>
        </aside>

        <!-- Main Body -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header/Navbar -->
            <header class="h-16 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 z-10 sticky top-0">
                <button
                    @click="adminStore.toggleSidebar"
                    class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors hidden lg:block"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="lg:hidden flex items-center gap-2">
                    <div class="w-8 h-8 bg-amber-500 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-bold">R</span>
                    </div>
                    <span class="font-bold text-md text-gray-900 dark:text-white">
                        {{ siteConfig.business_name || 'Rima Craft' }}
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-4">
                    <!-- Theme Toggle -->
                    <button
                        @click="themeStore.toggle"
                        class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <svg v-if="themeStore.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- User Info & Logout -->
                    <div class="flex items-center gap-3 border-l border-gray-200 dark:border-gray-800 pl-4">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-semibold text-gray-900 dark:text-white">{{ user.name }}</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                        </div>
                        <button
                            @click="logout"
                            class="p-2 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors"
                            title="Keluar"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 pb-24 lg:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation Bar -->
        <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 z-50 px-2 py-1 shadow-lg">
            <div class="flex justify-around items-center">
                <!-- Dashboard -->
                <Link
                    v-if="flatNavigation.some(item => item.route === 'dashboard')"
                    :href="route('dashboard')"
                    :class="[
                        'flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center',
                        route().current('dashboard') ? 'text-amber-500 font-bold' : 'text-gray-500 dark:text-gray-400'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[10px]">Dashboard</span>
                </Link>

                <!-- Penjualan -->
                <Link
                    v-if="flatNavigation.some(item => item.route === 'sales.index')"
                    :href="route('sales.index')"
                    :class="[
                        'flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center',
                        route().current('sales.index') ? 'text-amber-500 font-bold' : 'text-gray-500 dark:text-gray-400'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-6 2h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[10px]">Penjualan</span>
                </Link>

                <!-- Buku Kas -->
                <Link
                    v-if="flatNavigation.some(item => item.route === 'finance.index')"
                    :href="route('finance.index')"
                    :class="[
                        'flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-center',
                        route().current('finance.index') ? 'text-amber-500 font-bold' : 'text-gray-500 dark:text-gray-400'
                    ]"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[10px]">Buku Kas</span>
                </Link>

                <!-- Lainnya (Menu Drawer Trigger) -->
                <button
                    type="button"
                    @click="isMobileMenuOpen = true"
                    class="flex flex-col items-center gap-1 py-1 px-3 rounded-lg text-gray-500 dark:text-gray-400 text-center"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="text-[10px]">Menu</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Slider Overlay Drawer -->
        <div v-show="isMobileMenuOpen" class="lg:hidden fixed inset-0 z-[100] flex">
            <!-- Backdrop overlay -->
            <div @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <!-- Panel drawer content -->
            <div class="relative flex flex-col w-4/5 max-w-xs h-full bg-white dark:bg-gray-900 border-r border-gray-250 dark:border-gray-800 shadow-2xl p-5 z-10 transition-transform duration-300">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-800 mb-4">
                    <span class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <div class="w-6 h-6 bg-amber-500 rounded flex items-center justify-center">
                            <span class="text-white font-bold text-xs">R</span>
                        </div>
                        <span>Menu Navigasi</span>
                    </span>
                    <button @click="isMobileMenuOpen = false" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Navigation List inside Drawer -->
                <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                    <div v-for="category in categorizedNavigation" :key="category.title" class="space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest block mb-1">
                            {{ category.title }}
                        </span>
                        <Link
                            v-for="item in category.items"
                            :key="item.name"
                            :href="route(item.route)"
                            @click="isMobileMenuOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2 rounded-lg text-xs font-semibold transition-all',
                                route().current(item.route)
                                    ? 'bg-amber-500 text-gray-950 shadow-md shadow-amber-500/10'
                                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800/60 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            <span class="truncate">{{ item.name }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
