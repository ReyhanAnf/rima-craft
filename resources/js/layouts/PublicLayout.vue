<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Navbar from '@/components/Navbar.vue';
import CartDrawer from '@/components/CartDrawer.vue';
import Toast from '@/components/Toast.vue';

// Accept an explicit config override (e.g. from per-page props)
// Falls back to the globally shared siteConfig from HandleInertiaRequests
const props = defineProps({
    config: { type: Object, default: null },
});

const page       = usePage();
const siteConfig = computed(() => props.config ?? page.props.siteConfig ?? {});
const isCatalogPage = computed(() => String(page.component || '').includes('CatalogPage'));

const cartDrawer = ref(null);
const isInstallable = ref(typeof window !== 'undefined' ? !!window.isAppInstallable : false);

function openCart() {
    cartDrawer.value?.open();
}

const authUser = computed(() => page.props.auth?.user);

const dashboardRouteName = computed(() => {
    if (!page.props.auth?.roles) return null;
    const roles = page.props.auth.roles;
    if (roles.includes('customer')) return 'customer.dashboard';
    if (roles.includes('reseller')) return 'reseller.dashboard';
    if (roles.some(r => ['super-admin', 'owner', 'operator'].includes(r))) return 'dashboard';
    return null;
});

const businessName    = computed(() => siteConfig.value.business_name    ?? 'Rima Craft');
const businessPhone   = computed(() => siteConfig.value.business_phone   ?? '6281234567890');
const heroDescription = computed(() => siteConfig.value.hero_description ?? 'Menghadirkan mahakarya kerajinan Nusantara.');
const checkoutUrl     = computed(() => siteConfig.value.checkout_url     ?? '/checkout');
const loginUrl        = computed(() => siteConfig.value.login_url        ?? '/login');
const termsUrl        = computed(() => siteConfig.value.terms_url        ?? '/syarat-ketentuan');
const privacyUrl      = computed(() => siteConfig.value.privacy_url      ?? '/kebijakan-privasi');
const shippingUrl     = computed(() => siteConfig.value.shipping_url     ?? '/pengiriman-retur');
const year            = new Date().getFullYear();

if (typeof window !== 'undefined') {
    window.addEventListener('pwa-installable-status', (e) => {
        isInstallable.value = e.detail;
    });
}

const installPWA = async () => {
    const promptEvent = typeof window !== 'undefined' ? window.deferredInstallPrompt : null;
    if (!promptEvent) return;

    promptEvent.prompt();

    const { outcome } = await promptEvent.userChoice;
    if (outcome === 'accepted') {
        if (typeof window !== 'undefined') {
            window.deferredInstallPrompt = null;
            window.isAppInstallable = false;
        }
        isInstallable.value = false;
    }
};
</script>

<template>
        <div>
            <Navbar :business-name="businessName" @open-cart="openCart" />

            <button
                v-if="isInstallable && !isCatalogPage"
                type="button"
                @click="installPWA"
                class="fixed bottom-20 right-4 z-50 inline-flex items-center gap-2 rounded-full bg-amber-500 px-4 py-3 text-sm font-bold text-gray-950 shadow-lg shadow-amber-500/20 transition hover:bg-amber-600 lg:hidden"
                title="Download App"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download App
            </button>

        <main class="min-h-screen pb-24">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-[#020202] border-t border-gray-200 dark:border-[#1a1a1a] pt-16 pb-8 transition-colors duration-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-16">
                    <div class="md:col-span-2">
                        <h2 class="text-2xl font-serif font-bold tracking-widest text-gray-900 dark:text-white uppercase mb-6">{{ businessName }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 font-light leading-relaxed max-w-md">{{ heroDescription }}</p>
                    </div>

                    <div>
                        <ul class="space-y-4 font-light text-sm text-gray-500 dark:text-gray-400">
                            <li><a href="#" @click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Beranda</a></li>
                            <li><a href="#katalog" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Koleksi Kami</a></li>
                            <li><a href="#kontak" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Lokasi & Kontak</a></li>
                        </ul>
                    </div>

                    <div>
                        <ul class="space-y-4 font-light text-sm text-gray-500 dark:text-gray-400">
                            <li><a :href="termsUrl" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Syarat & Ketentuan</a></li>
                            <li><a :href="privacyUrl" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Kebijakan Privasi</a></li>
                            <li><a :href="shippingUrl" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Pengiriman & Retur</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Sponsors Row -->
                <div v-if="siteConfig.sponsor_1_name || siteConfig.sponsor_2_name || siteConfig.sponsor_3_name || siteConfig.sponsor_4_name" class="pt-8 border-t border-gray-200 dark:border-[#1a1a1a] mb-8">
                    <p class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Didukung & Disponsori Oleh</p>
                    <div class="flex flex-wrap items-center gap-8">
                        <div v-if="siteConfig.sponsor_1_name" class="flex items-center gap-2">
                            <img v-if="siteConfig.sponsor_1_logo_url" :src="`/storage/${siteConfig.sponsor_1_logo_url}`" class="h-7 w-auto object-contain rounded bg-white p-0.5 border border-gray-200" :alt="siteConfig.sponsor_1_name" />
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ siteConfig.sponsor_1_name }}</span>
                        </div>
                        <div v-if="siteConfig.sponsor_2_name" class="flex items-center gap-2">
                            <img v-if="siteConfig.sponsor_2_logo_url" :src="`/storage/${siteConfig.sponsor_2_logo_url}`" class="h-7 w-auto object-contain rounded bg-white p-0.5 border border-gray-200" :alt="siteConfig.sponsor_2_name" />
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ siteConfig.sponsor_2_name }}</span>
                        </div>
                        <div v-if="siteConfig.sponsor_3_name" class="flex items-center gap-2">
                            <img v-if="siteConfig.sponsor_3_logo_url" :src="`/storage/${siteConfig.sponsor_3_logo_url}`" class="h-7 w-auto object-contain rounded bg-white p-0.5 border border-gray-200" :alt="siteConfig.sponsor_3_name" />
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ siteConfig.sponsor_3_name }}</span>
                        </div>
                        <div v-if="siteConfig.sponsor_4_name" class="flex items-center gap-2">
                            <img v-if="siteConfig.sponsor_4_logo_url" :src="`/storage/${siteConfig.sponsor_4_logo_url}`" class="h-7 w-auto object-contain rounded bg-white p-0.5 border border-gray-200" :alt="siteConfig.sponsor_4_name" />
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ siteConfig.sponsor_4_name }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-gray-200 dark:border-[#1a1a1a] flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="text-xs text-gray-500 font-light tracking-wide">
                        &copy; {{ year }} {{ businessName }}. Hak cipta dilindungi.
                    </div>
                    <div class="flex items-center gap-6">
                        <Link v-if="authUser && dashboardRouteName" :href="route(dashboardRouteName)" class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 hover:text-amber-600 dark:hover:text-amber-500 transition-colors duration-300">Portal Saya</Link>
                        <a v-else :href="loginUrl" class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 hover:text-amber-600 dark:hover:text-amber-500 transition-colors duration-300">Login</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Cart Drawer -->
        <CartDrawer
            ref="cartDrawer"
            :business-phone="businessPhone"
            :business-name="businessName"
            :checkout-url="checkoutUrl"
        />

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>
