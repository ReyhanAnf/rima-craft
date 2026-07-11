<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';
// No axios import — use window.apiFetch (project rule: no raw fetch)

const props = defineProps({
    products:  { type: Array, default: () => [] },
    galleries: { type: Array, default: () => [] },
    settings:  { type: Object, default: () => ({}) },
});

const config = usePage().props.siteConfig ?? {};
const isInstallable = ref(typeof window !== 'undefined' ? !!window.isAppInstallable : false);
const cart   = useCartStore();
const toast  = useToastStore();

// ── Filter state ────────────────────────────────────────────
const search      = ref('');
const stockFilter = ref('semua');
const sortBy      = ref('default');
const loading     = ref(false);
const filteredProducts = ref([...props.products]);

async function applyFilter() {
    loading.value = true;
    try {
        const params = new URLSearchParams({ search: search.value, stock: stockFilter.value });
        const data = await window.apiFetch(`/katalog/filter?${params}`, { method: 'GET' });
        filteredProducts.value = data.products;
    } catch {
        toast.error('Gagal memuat produk. Silakan coba lagi.');
    } finally {
        loading.value = false;
    }
}

let debounceTimer = null;
function onSearchInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(applyFilter, 400);
}
function onStockChange() { applyFilter(); }

const displayedProducts = computed(() => {
    let items = [...filteredProducts.value];
    if (sortBy.value === 'price-asc') {
        items.sort((a, b) => (a.pricing?.price ?? Number(a.base_price)) - (b.pricing?.price ?? Number(b.base_price)));
    } else if (sortBy.value === 'price-desc') {
        items.sort((a, b) => (b.pricing?.price ?? Number(b.base_price)) - (a.pricing?.price ?? Number(a.base_price)));
    } else if (sortBy.value === 'discount') {
        items.sort((a, b) => (b.discount_percentage || 0) - (a.discount_percentage || 0));
    }
    return items;
});

// ── Quantity Picker (per card) ───────────────────────────────
// activeQtyPicker: product.id that shows the qty picker overlay
const activeQtyPicker = ref(null);
const pickerQty       = ref(1);

function openQtyPicker(product, e) {
    e.stopPropagation();
    if (product.current_stock <= 0) return;
    activeQtyPicker.value = product.id;
    pickerQty.value = 1;
}

function closeQtyPicker() {
    activeQtyPicker.value = null;
    pickerQty.value = 1;
}

function confirmAddToCart(product) {
    const result = cart.add({
        id:    product.id,
        name:  product.name,
        price: product.pricing?.price ?? Number(product.base_price),
        stock: product.current_stock,
        image: product.image_path ? `/storage/${product.image_path}` : null,
    }, pickerQty.value);

    if (result?.success) toast.success(result.success);
    else if (result?.error) toast.error(result.error);
    closeQtyPicker();
}

// ── Product Drawer ──────────────────────────────────────────
const drawerProduct    = ref(null);
const drawerOpen       = ref(false);
const activeImageIndex = ref(0);
const selectedVariant  = ref(null);

// Build gallery array from main image + media_assets images
const drawerGallery = computed(() => {
    if (!drawerProduct.value) return [];
    const imgs = [];
    if (drawerProduct.value.image_path) {
        imgs.push({ src: `/storage/${drawerProduct.value.image_path}`, type: 'image' });
    }
    (drawerProduct.value.media_assets || []).forEach(m => {
        if (m.type === 'image') {
            const src = m.url.startsWith('/') ? m.url : `/${m.url}`;
            imgs.push({ src, type: 'image' });
        }
    });
    return imgs;
});

const drawerVariants = computed(() => drawerProduct.value?.variants ?? []);

const drawerPrice = computed(() => {
    if (!drawerProduct.value) return 0;
    const base = drawerProduct.value.pricing?.price ?? Number(drawerProduct.value.base_price);
    if (selectedVariant.value && selectedVariant.value.price_adj) {
        return base + Number(selectedVariant.value.price_adj);
    }
    return base;
});

function openDrawer(product) {
    drawerProduct.value    = product;
    drawerOpen.value       = true;
    activeImageIndex.value = 0;
    selectedVariant.value  = null;
    document.body.style.overflow = 'hidden';
}

function closeDrawer() {
    drawerOpen.value = false;
    document.body.style.overflow = '';
    setTimeout(() => { drawerProduct.value = null; }, 350);
}

// qty in drawer
const drawerQty = ref(1);
watch(drawerProduct, () => { drawerQty.value = 1; });

function drawerAddToCart() {
    if (!drawerProduct.value) return;
    const hasVariants = drawerVariants.value.length > 0;
    if (hasVariants && !selectedVariant.value) {
        toast.error('Silakan pilih varian terlebih dahulu.');
        return;
    }
    const result = cart.add({
        id:           drawerProduct.value.id,
        name:         drawerProduct.value.name,
        price:        drawerPrice.value,
        stock:        drawerProduct.value.current_stock,
        image:        drawerProduct.value.image_path ? `/storage/${drawerProduct.value.image_path}` : null,
        variantLabel: selectedVariant.value?.label ?? null,
    }, drawerQty.value);

    if (result?.success) { toast.success(result.success); closeDrawer(); }
    else if (result?.error) toast.error(result.error);
}

// ── Price helpers ───────────────────────────────────────────
function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}

function openWa() {
    if (waPhone.value) window.open(`https://wa.me/${waPhone.value}`, '_blank');
}

// ── Page-level computed ─────────────────────────────────────
const heroImageUrl = computed(() =>
    props.settings.hero_image_url ? `/storage/${props.settings.hero_image_url}` : '/assets/landing/hero.png'
);
const loopingVideoUrl = computed(() => {
    const v = props.settings.looping_video_url;
    if (!v) return null;
    return v.startsWith('http') ? v : `/storage/${v}`;
});
const videoUrl  = computed(() => props.settings.video_url ?? 'https://www.youtube.com/embed/ScMzIvxBSi4');
const gmapsUrl  = computed(() => props.settings.gmaps_iframe ?? '');

const waPhone = computed(() => {
    const raw = (props.settings.business_phone ?? '').replace(/\D/g, '');
    return raw.startsWith('0') ? '62' + raw.substring(1) : raw;
});

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
    <PublicLayout>
        <button
            v-if="isInstallable"
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

        <!-- Hero Section -->
        <div class="relative w-full h-[85vh] min-h-[600px] flex items-center justify-center overflow-hidden -mt-20 pt-20">
            <div class="absolute inset-0 z-0 bg-gray-100 dark:bg-gray-900">
                <img :src="heroImageUrl" alt="Rima Craft" class="w-full h-full object-cover opacity-70 dark:opacity-80 mix-blend-multiply dark:mix-blend-overlay" />
                <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/70 to-white/10 dark:from-black/90 dark:via-black/60 dark:to-transparent"></div>
            </div>
            <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-start justify-center">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-gray-900 dark:text-white tracking-wide mb-6 leading-[1.1] max-w-3xl">
                    {{ settings.hero_title_1 ?? 'Seni Anyaman Tradisional' }}<br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-amber-400 dark:from-amber-300 dark:to-amber-500">
                        {{ settings.hero_title_2 ?? 'Bercita Rasa Modern' }}
                    </span>
                </h1>
                <p class="text-lg md:text-xl text-gray-700 dark:text-gray-200 max-w-2xl font-medium mb-10 leading-relaxed">
                    {{ settings.hero_description ?? 'Temukan koleksi kerajinan eksklusif Rima Craft.' }}
                </p>
                <a href="#katalog" class="px-8 py-3.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-full shadow-lg shadow-amber-500/20 flex items-center gap-2 hover:scale-105 active:scale-95 transition-all">
                    Mulai Belanja
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>

        <!-- Cinematic Video Showcase -->
        <section class="bg-[#f7f3ed] dark:bg-gray-950 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-5 min-h-[85vh]">
                <div class="flex flex-col justify-center px-8 py-20 lg:py-28 lg:px-16 xl:px-24 order-2 lg:order-1 lg:col-span-3">
                    <div class="mb-10">
                        <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                            <span class="block h-px w-10 bg-amber-400"></span>
                            <span>Cara kami bekerja</span>
                        </p>
                        <h2 class="max-w-lg">
                            <span class="block font-serif italic text-3xl md:text-4xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Proses anyaman</span>
                            <span class="block text-2xl md:text-3xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dari bahan mentah ke produk jadi.</span>
                        </h2>
                    </div>

                    <p class="text-[15px] md:text-base text-gray-600 dark:text-gray-300 leading-[1.8] max-w-md mb-14">
                        Rotan dan pandan diolah oleh pengrajin lokal. Dikupas, dikeringkan, lalu dianyam satu per satu.
                    </p>

                    <div class="grid sm:grid-cols-2 gap-8 max-w-lg">
                        <div class="border-l-2 border-amber-400 pl-5">
                            <span class="text-[11px] font-medium text-amber-600 dark:text-amber-400 tabular-nums mb-2 block">01</span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1.5">Anyaman manual</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Setiap produk dianyam tangan, tanpa mesin produksi massal.</p>
                        </div>
                        <div class="border-l-2 border-stone-300 dark:border-stone-600 pl-5">
                            <span class="text-[11px] font-medium text-stone-500 dark:text-stone-400 tabular-nums mb-2 block">02</span>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-1.5">Sortir bahan</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">Bahan diseleksi sebelum masuk proses anyaman.</p>
                        </div>
                    </div>
                </div>
                <div class="relative w-full h-[60vh] lg:h-auto order-1 lg:order-2 bg-gray-100 dark:bg-gray-900 lg:col-span-2">
                    <video v-if="loopingVideoUrl" autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover">
                        <source :src="loopingVideoUrl" type="video/mp4" />
                    </video>
                    <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                        <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span class="font-bold uppercase tracking-widest text-xs">Video Belum Diunggah</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Gallery Section -->
        <section class="py-16 md:py-24 bg-white dark:bg-gray-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-10 md:mb-14 md:flex md:items-end md:justify-between md:gap-12">
                    <div class="max-w-md">
                        <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                            <span class="block h-px w-10 bg-amber-400"></span>
                            <span>Galeri</span>
                        </p>
                        <h2 class="max-w-sm">
                            <span class="block font-serif italic text-2xl md:text-3xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Foto workshop</span>
                            <span class="block text-xl md:text-2xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dan produk jadi.</span>
                        </h2>
                    </div>
                    <p class="mt-6 md:mt-0 text-[15px] text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs md:text-right">
                        Dokumentasi proses anyaman dan hasil karya dari workshop kami.
                    </p>
                </div>

                <div v-if="galleries.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-x-5 md:gap-y-10">
                    <figure
                        v-for="(gallery, i) in galleries"
                        :key="gallery.id"
                        :class="[
                            i === 0 ? 'sm:col-span-2 lg:col-span-2' : '',
                            i % 4 === 3 ? 'lg:col-span-2' : '',
                        ]"
                        class="group"
                    >
                        <div
                            :class="[
                                i === 0 ? 'aspect-[16/10] sm:aspect-[21/9]' : i % 3 === 1 ? 'aspect-[3/4]' : 'aspect-[4/3]',
                            ]"
                            class="overflow-hidden bg-stone-100 dark:bg-gray-800"
                        >
                            <img
                                :src="`/storage/${gallery.image_url}`"
                                :alt="gallery.title || 'Galeri Rima Craft'"
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]"
                                loading="lazy"
                            />
                        </div>
                        <figcaption v-if="gallery.label || gallery.title" class="pt-3 flex items-baseline justify-between gap-3 px-0.5">
                            <p v-if="gallery.title" class="text-sm font-medium text-gray-900 dark:text-white leading-snug">{{ gallery.title }}</p>
                            <span v-if="gallery.label" class="text-[11px] text-stone-400 dark:text-stone-500 shrink-0">{{ gallery.label }}</span>
                        </figcaption>
                    </figure>
                </div>

                <div v-else class="py-16 md:py-20 border border-dashed border-stone-200 dark:border-stone-700 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada foto galeri.</p>
                </div>
            </div>
        </section>

        <!-- Video Storytelling -->
        <section class="py-16 md:py-24 bg-stone-50 dark:bg-gray-950">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 md:mb-10 md:flex md:items-end md:justify-between md:gap-12">
                    <div class="max-w-md">
                        <p class="text-sm text-amber-700 dark:text-amber-400 mb-5 flex items-center gap-3">
                            <span class="block h-px w-10 bg-amber-400"></span>
                            <span>Video proses</span>
                        </p>
                        <h2 class="max-w-sm">
                            <span class="block font-serif italic text-2xl md:text-3xl text-gray-800 dark:text-gray-100 leading-snug mb-1">Proses di workshop</span>
                            <span class="block text-xl md:text-2xl font-semibold text-gray-900 dark:text-white tracking-tight leading-snug">dalam video singkat.</span>
                        </h2>
                    </div>
                    <p class="mt-6 md:mt-0 text-[15px] text-gray-500 dark:text-gray-400 leading-relaxed max-w-xs md:text-right">
                        Cuplikan pekerjaan anyaman langsung dari bengkel pengrajin kami.
                    </p>
                </div>

                <div class="relative aspect-video w-full overflow-hidden bg-stone-900 border border-stone-200 dark:border-stone-700">
                    <iframe
                        class="absolute inset-0 w-full h-full"
                        :src="`${videoUrl}?rel=0&modestbranding=1`"
                        title="Video proses anyaman Rima Craft"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════
             Product Catalog
        ════════════════════════════════════════════════════════ -->
        <section id="katalog" class="py-16 lg:py-28 bg-[#faf8f5] dark:bg-[#080808]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 md:mb-12 gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2.5">
                            <div class="h-px w-6 bg-amber-500"></div>
                            <span class="text-amber-600 dark:text-amber-500 font-semibold tracking-[0.28em] uppercase text-[10px]">Koleksi</span>
                        </div>
                        <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white leading-tight">Produk Pilihan</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5 max-w-sm">Karya seni fungsional yang dibuat dengan tangan, untuk Anda miliki.</p>
                    </div>
                    <Link :href="route('catalog.shop')" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:text-amber-600 dark:hover:text-amber-400 transition-colors self-start sm:self-auto shrink-0">
                        Lihat semua
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-gray-300 dark:border-gray-700 group-hover:border-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </span>
                    </Link>
                </div>

                <!-- Filter Bar -->
                <div id="katalog-filter" class="mb-8 flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
                    <!-- Search -->
                    <div class="relative flex-1 sm:max-w-xs">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input v-model="search" @input="onSearchInput" type="search" placeholder="Cari produk..." class="w-full pl-9 pr-4 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111] text-gray-900 dark:text-white placeholder-gray-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 outline-none transition-all" />
                    </div>
                    <!-- Selects — full width on mobile -->
                    <div class="grid grid-cols-2 sm:flex gap-2">
                        <select v-model="stockFilter" @change="onStockChange" class="px-3 py-2.5 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111] text-gray-700 dark:text-gray-300 focus:border-amber-500 outline-none transition-all cursor-pointer">
                            <option value="semua">Semua Stok</option>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Stok Habis</option>
                        </select>
                        <select v-model="sortBy" class="px-3 py-2.5 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#111] text-gray-700 dark:text-gray-300 focus:border-amber-500 outline-none transition-all cursor-pointer">
                            <option value="default">Default</option>
                            <option value="price-asc">Harga ↑</option>
                            <option value="price-desc">Harga ↓</option>
                            <option value="discount">Diskon</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div
                    class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5 transition-opacity duration-300"
                    :class="{ 'opacity-40 pointer-events-none': loading }"
                >
                    <!-- Empty state -->
                    <div v-if="displayedProducts.length === 0 && !loading" class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-600">
                        <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium">Tidak ada produk ditemukan</p>
                    </div>

                    <!-- ═══ PRODUCT CARD ═══ -->
                    <div
                        v-for="product in displayedProducts"
                        :key="product.id"
                        class="group relative flex flex-col bg-white dark:bg-[#111] rounded-2xl sm:rounded-3xl overflow-hidden ring-1 ring-black/5 dark:ring-white/5 hover:ring-amber-400/40 dark:hover:ring-amber-500/30 hover:shadow-xl hover:shadow-amber-500/10 transition-all duration-500"
                    >
                        <!-- Image -->
                        <div class="relative aspect-[3/4] overflow-hidden bg-gray-100 dark:bg-[#1a1a1a] cursor-pointer" @click="openDrawer(product)">
                            <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-700 ease-out" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>

                            <!-- Badges — compact on mobile -->
                            <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-col gap-1">
                                <span v-if="product.current_stock <= 0" class="px-1.5 py-0.5 bg-black/70 text-white text-[8px] sm:text-[9px] font-bold uppercase tracking-wider rounded-full backdrop-blur-sm">Habis</span>
                                <span v-else-if="product.discount_percentage > 0" class="px-1.5 py-0.5 bg-rose-500 text-white text-[8px] sm:text-[9px] font-bold rounded-full">−{{ product.discount_percentage }}%</span>
                                <span v-if="(product.variants ?? []).length > 0" class="px-1.5 py-0.5 bg-amber-500 text-white text-[8px] sm:text-[9px] font-bold rounded-full">Variasi</span>
                            </div>

                            <!-- Gallery count -->
                            <div v-if="(product.media_assets ?? []).filter(m => m.type === 'image').length > 0" class="absolute bottom-2 right-2 sm:bottom-3 sm:right-3 flex items-center gap-0.5 px-1.5 py-0.5 bg-black/55 text-white text-[8px] sm:text-[9px] font-semibold rounded-full backdrop-blur-sm">
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ (product.media_assets ?? []).filter(m => m.type === 'image').length + 1 }}
                            </div>

                            <!-- Hover CTA (desktop only) -->
                            <div class="hidden sm:flex absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-400 items-end justify-center pb-5">
                                <span class="px-4 py-2 bg-white text-gray-900 text-[10px] font-bold rounded-full shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 uppercase tracking-widest">
                                    Detail &amp; Gambar
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-3 sm:p-4 flex-1 flex flex-col gap-2">
                            <h3 class="font-serif font-semibold text-xs sm:text-sm leading-snug text-gray-900 dark:text-white line-clamp-2 cursor-pointer hover:text-amber-600 dark:hover:text-amber-400 transition-colors" @click="openDrawer(product)">
                                {{ product.name }}
                            </h3>

                            <!-- Price row -->
                            <div class="flex items-baseline gap-1.5 mt-auto flex-wrap">
                                <span class="text-sm sm:text-base font-black text-amber-600 dark:text-amber-400 font-serif tracking-tight leading-none">
                                    {{ formatPrice(product.pricing?.price ?? product.base_price) }}
                                </span>
                                <span v-if="product.pricing?.has_discount" class="text-[9px] sm:text-[10px] text-gray-400 line-through leading-none">
                                    {{ formatPrice(product.pricing.base_price) }}
                                </span>
                            </div>

                            <!-- CTA button -->
                            <button
                                @click="openDrawer(product)"
                                :disabled="product.current_stock <= 0"
                                class="w-full mt-1 py-2 sm:py-2.5 rounded-xl sm:rounded-2xl text-[10px] sm:text-[11px] font-bold uppercase tracking-wide transition-all duration-200 flex items-center justify-center gap-1"
                                :class="product.current_stock <= 0
                                    ? 'border border-gray-200 dark:border-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                                    : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 hover:bg-amber-500 hover:text-white dark:hover:bg-amber-500 dark:hover:text-gray-950 border border-amber-200 dark:border-amber-500/30 hover:border-amber-500 active:scale-[0.97]'"
                            >
                                <svg v-if="product.current_stock > 0" class="w-3 h-3 sm:w-3.5 sm:h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>{{ product.current_stock <= 0 ? 'Habis' : 'Beli' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════════════
             PRODUCT DETAIL DRAWER (dari kanan)
        ════════════════════════════════════════════════════════ -->

        <!-- Backdrop -->
        <Teleport to="body">
            <div
                v-if="drawerProduct"
                @click="closeDrawer"
                class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[200] transition-opacity duration-300"
                :class="drawerOpen ? 'opacity-100' : 'opacity-0'"
            ></div>

            <!-- Drawer panel -->
            <div
                v-if="drawerProduct"
                class="fixed top-0 right-0 h-full w-full sm:w-[480px] max-w-full bg-white dark:bg-[#111] z-[201] flex flex-col shadow-2xl transition-transform duration-350 ease-in-out"
                :class="drawerOpen ? 'translate-x-0' : 'translate-x-full'"
            >
                <!-- Drawer Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-[#1e1e1e] flex-shrink-0">
                    <div>
                        <span class="text-[9px] font-bold text-amber-600/70 dark:text-amber-500/60 uppercase tracking-[0.2em] block">Detail Produk</span>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">Rima Craft Signature</p>
                    </div>
                    <button
                        @click="closeDrawer"
                        class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Scrollable content -->
                <div class="flex-1 overflow-y-auto">

                    <!-- ── Image Gallery ── -->
                    <div class="relative">
                        <!-- Main image -->
                        <div class="aspect-[4/3] bg-gray-50 dark:bg-gray-900 overflow-hidden">
                            <img
                                v-if="drawerGallery.length > 0"
                                :src="drawerGallery[activeImageIndex]?.src"
                                :alt="drawerProduct.name"
                                class="w-full h-full object-cover transition-opacity duration-300"
                                :key="activeImageIndex"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <!-- Image counter pill -->
                            <div v-if="drawerGallery.length > 1" class="absolute bottom-3 right-3 px-2.5 py-1 bg-black/60 text-white text-[10px] font-semibold rounded-full backdrop-blur-sm">
                                {{ activeImageIndex + 1 }} / {{ drawerGallery.length }}
                            </div>
                        </div>

                        <!-- Thumbnail strip -->
                        <div v-if="drawerGallery.length > 1" class="flex gap-2 px-4 py-3 overflow-x-auto hide-scrollbar bg-white dark:bg-[#111]">
                            <button
                                v-for="(img, idx) in drawerGallery"
                                :key="idx"
                                @click="activeImageIndex = idx"
                                class="flex-none w-14 h-14 rounded-lg overflow-hidden border-2 transition-all duration-200"
                                :class="activeImageIndex === idx
                                    ? 'border-amber-500 ring-2 ring-amber-500/30 scale-105'
                                    : 'border-gray-200 dark:border-gray-700 opacity-60 hover:opacity-100'"
                            >
                                <img :src="img.src" :alt="`foto ${idx + 1}`" class="w-full h-full object-cover" />
                            </button>
                        </div>
                    </div>

                    <!-- ── Product Info ── -->
                    <div class="px-5 py-4 space-y-5">

                        <!-- Name + Stock -->
                        <div>
                            <h3 class="text-xl font-serif font-bold text-gray-900 dark:text-white leading-snug mb-3">
                                {{ drawerProduct.name }}
                            </h3>
                            <div class="flex items-center gap-2.5">
                                <span :class="['px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider',
                                    drawerProduct.current_stock > 0
                                        ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                        : 'bg-red-50 dark:bg-red-500/10 text-red-500']">
                                    {{ drawerProduct.current_stock > 0 ? 'Tersedia' : 'Habis' }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">Stok: {{ drawerProduct.current_stock }} pcs</span>
                            </div>
                        </div>

                        <!-- Price box -->
                        <div class="bg-amber-50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-500/10 rounded-2xl px-4 py-3">
                            <div class="text-[10px] text-amber-700/60 dark:text-amber-400/60 font-semibold uppercase tracking-wider mb-1">Harga</div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-black text-amber-600 dark:text-amber-400 font-serif">{{ formatPrice(drawerPrice) }}</span>
                                <span v-if="drawerProduct.pricing?.has_discount && !selectedVariant" class="text-sm text-gray-400 line-through">{{ formatPrice(drawerProduct.pricing.base_price) }}</span>
                            </div>
                            <div v-if="selectedVariant?.price_adj > 0" class="text-[10px] text-amber-600/70 dark:text-amber-400/60 mt-1">
                                Harga dasar + Rp {{ Number(selectedVariant.price_adj).toLocaleString('id-ID') }} (varian {{ selectedVariant.label }})
                            </div>
                        </div>

                        <!-- Variant Selector -->
                        <div v-if="drawerVariants.length > 0">
                            <h4 class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2.5">Pilih Varian</h4>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="(variant, idx) in drawerVariants"
                                    :key="idx"
                                    @click="selectedVariant = (selectedVariant?.label === variant.label ? null : variant)"
                                    class="px-3.5 py-1.5 rounded-full border text-xs font-semibold transition-all duration-200"
                                    :class="selectedVariant?.label === variant.label
                                        ? 'border-amber-500 bg-amber-500 text-white shadow-md shadow-amber-500/20'
                                        : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-amber-400 hover:text-amber-600 dark:hover:text-amber-400'"
                                >
                                    {{ variant.label }}
                                    <span v-if="variant.price_adj > 0" class="opacity-70 ml-1">+{{ formatPrice(variant.price_adj) }}</span>
                                </button>
                            </div>
                            <p v-if="drawerVariants.length > 0 && !selectedVariant" class="text-[10px] text-amber-600/80 dark:text-amber-400/60 mt-2">* Silakan pilih salah satu varian</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <h4 class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Deskripsi Produk</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-line">
                                {{ drawerProduct.description || 'Produk premium khas dari Rima Craft. Setiap jengkal anyaman diproduksi secara teliti oleh pengrajin ahli lokal untuk memastikan keindahan dan ketahanan yang optimal.' }}
                            </p>
                        </div>

                    </div>
                </div>

                <!-- ── Drawer Footer — Qty + Add to Cart ── -->
                <div class="flex-shrink-0 border-t border-gray-100 dark:border-[#1e1e1e] px-5 py-4 bg-white dark:bg-[#111]">
                    <div v-if="drawerProduct.current_stock > 0" class="flex items-center gap-3 mb-3">
                        <div class="flex items-center border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <button
                                @click="drawerQty = Math.max(1, drawerQty - 1)"
                                class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-bold text-lg"
                            >−</button>
                            <span class="w-12 text-center font-extrabold text-gray-900 dark:text-white text-base">{{ drawerQty }}</span>
                            <button
                                @click="drawerQty = Math.min(drawerProduct.current_stock, drawerQty + 1)"
                                class="w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors font-bold text-lg"
                            >+</button>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            Maks. <span class="font-semibold text-gray-600 dark:text-gray-300">{{ drawerProduct.current_stock }}</span> pcs
                        </div>
                    </div>

                    <button
                        @click="drawerAddToCart"
                        :disabled="drawerProduct.current_stock <= 0"
                        class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 active:scale-[0.98] disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm transition-all shadow-lg shadow-amber-500/20 hover:shadow-xl hover:shadow-amber-500/30 flex items-center justify-center gap-2"
                    >
                        <svg v-if="drawerProduct.current_stock > 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        {{ drawerProduct.current_stock <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Contact / Lokasi Section -->
        <section id="kontak" class="bg-gray-950 dark:bg-[#030303] relative overflow-hidden">

            <!-- Full-width Map -->
            <div class="relative w-full h-64 sm:h-80 lg:h-[520px]">
                <iframe
                    v-if="gmapsUrl"
                    :src="gmapsUrl"
                    class="absolute inset-0 w-full h-full border-0 grayscale opacity-80"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                ></iframe>
                <div v-else class="absolute inset-0 bg-gray-900 flex items-center justify-center">
                    <div class="flex flex-col items-center gap-3 text-gray-600">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="text-sm font-medium tracking-wide">Peta belum dikonfigurasi</span>
                    </div>
                </div>
                <!-- gradient overlay bottom -->
                <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-gray-950 to-transparent"></div>
            </div>

            <!-- Info Cards pulled up over the map bottom -->
            <div class="relative -mt-8 pb-16 px-4 sm:px-6 lg:px-8">
                <div class="max-w-6xl mx-auto">

                    <!-- Section label -->
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-px flex-1 max-w-[2rem] bg-amber-500"></div>
                        <span class="text-amber-500 text-[10px] font-bold uppercase tracking-[0.3em]">Kontak & Lokasi</span>
                    </div>

                    <!-- Card grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                        <!-- Alamat card -->
                        <div v-if="settings.address" class="lg:col-span-2 bg-white/5 hover:bg-white/8 border border-white/10 rounded-2xl p-5 transition-colors group">
                            <div class="flex items-start gap-4">
                                <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-1.5">Alamat Workshop</p>
                                    <p class="text-sm text-gray-200 leading-relaxed">{{ settings.address }}</p>
                                    <p v-if="settings.business_hours && settings.business_hours.trim()" class="mt-2.5 text-xs text-amber-400/80 font-medium">
                                        {{ settings.business_hours }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp card -->
                        <div v-if="waPhone" class="bg-white/5 hover:bg-[#25D366]/10 border border-white/10 hover:border-[#25D366]/30 rounded-2xl p-5 transition-all group cursor-pointer" @click="openWa">
                            <div class="flex flex-col h-full gap-3">
                                <div class="w-9 h-9 rounded-xl bg-[#25D366]/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#25D366]" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-1">WhatsApp</p>
                                    <p class="text-sm font-semibold text-gray-200 group-hover:text-[#25D366] transition-colors">Chat Sekarang</p>
                                </div>
                                <div class="flex items-center gap-1 text-[10px] text-[#25D366]/70 font-medium mt-auto">
                                    <span>Buka WhatsApp</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Email + Instagram card (stacked) -->
                        <div class="bg-white/5 hover:bg-white/8 border border-white/10 rounded-2xl p-5 transition-colors flex flex-col gap-4">
                            <div v-if="settings.email">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Email</p>
                                </div>
                                <a :href="`mailto:${settings.email}`" class="text-sm text-gray-300 hover:text-amber-400 transition-colors break-all font-medium">{{ settings.email }}</a>
                            </div>
                            <div v-if="settings.instagram" :class="settings.email ? 'border-t border-white/10 pt-4' : ''">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <svg class="w-3.5 h-3.5 text-pink-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    <p class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em]">Instagram</p>
                                </div>
                                <a :href="`https://instagram.com/${settings.instagram}`" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-300 hover:text-pink-400 transition-colors font-medium">@{{ settings.instagram }}</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </PublicLayout>
</template>

<style scoped>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(4px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
.animate-fade-in {
    animation: fade-in 0.15s ease-out forwards;
}

/* Smooth drawer transition */
.translate-x-full { transform: translateX(100%); }
.translate-x-0    { transform: translateX(0); }

.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
