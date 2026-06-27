<script setup>
import { ref, computed } from 'vue';
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
const cart   = useCartStore();
const toast  = useToastStore();

// Filter state
const search      = ref('');
const stockFilter = ref('semua');
const sortBy      = ref('default');
const loading     = ref(false);
const filteredProducts = ref([...props.products]);
const selectedProduct = ref(null);

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

function showAllProducts() {
    search.value = '';
    stockFilter.value = 'semua';
    sortBy.value = 'default';
    applyFilter();
    
    const el = document.getElementById('katalog-filter');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

const displayedProducts = computed(() => {
    let items = [...filteredProducts.value];
    if (sortBy.value === 'price-asc') {
        items.sort((a, b) => {
            const priceA = a.pricing?.price ?? Number(a.base_price);
            const priceB = b.pricing?.price ?? Number(b.base_price);
            return priceA - priceB;
        });
    } else if (sortBy.value === 'price-desc') {
        items.sort((a, b) => {
            const priceA = a.pricing?.price ?? Number(a.base_price);
            const priceB = b.pricing?.price ?? Number(b.base_price);
            return priceB - priceA;
        });
    } else if (sortBy.value === 'discount') {
        items.sort((a, b) => (b.discount_percentage || 0) - (a.discount_percentage || 0));
    }
    return items;
});

function addToCart(product) {
    const result = cart.add({
        id:    product.id,
        name:  product.name,
        price: product.pricing?.price ?? Number(product.base_price),
        stock: product.current_stock,
        image: product.image_path ? `/storage/${product.image_path}` : null,
    });
    if (result?.success) toast.success(result.success);
    else if (result?.error) toast.error(result.error);
}

function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}

const heroImageUrl  = computed(() => props.settings.hero_image_url
    ? `/storage/${props.settings.hero_image_url}` : '/assets/landing/hero.png');
const loopingVideoUrl = computed(() => {
    const v = props.settings.looping_video_url;
    if (!v) return null;
    return v.startsWith('http') ? v : `/storage/${v}`;
});
const videoUrl    = computed(() => props.settings.video_url ?? 'https://www.youtube.com/embed/ScMzIvxBSi4');
const gmapsUrl    = computed(() => props.settings.gmaps_iframe ?? '');

// Chunk galleries into pairs for the mosaic layout
const galleryChunks = computed(() => {
    const arr = props.galleries;
    const chunks = [];
    for (let i = 0; i < arr.length; i += 2) chunks.push(arr.slice(i, i + 2));
    return chunks;
});

// Format WA phone: strip non-digits, replace leading 0 with 62
const waPhone = computed(() => {
    const raw = (props.settings.business_phone ?? '').replace(/\D/g, '');
    return raw.startsWith('0') ? '62' + raw.substring(1) : raw;
});
</script>

<template>
    <PublicLayout>

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
        <section class="bg-white dark:bg-gray-950 overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-5 min-h-[85vh]">
                <div class="flex flex-col justify-center px-8 py-20 lg:py-28 lg:px-16 xl:px-24 order-2 lg:order-1 lg:col-span-3">
                    <span class="text-amber-600 dark:text-amber-500 font-bold tracking-widest uppercase text-xs mb-4 block">Di Balik Layar</span>
                    <h2 class="text-4xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white mb-6 leading-tight">Proses Pembuatan Produk Kami</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 font-light mb-12 leading-relaxed">Lihat bagaimana para pengrajin lokal kami memproses bahan mentah menjadi karya seni yang indah dan fungsional untuk Anda.</p>
                    <div class="space-y-8">
                        <div class="flex items-start gap-5">
                            <div class="mt-1.5 flex-shrink-0"><div class="w-2.5 h-2.5 rounded-full bg-amber-500 ring-4 ring-amber-500/20"></div></div>
                            <div>
                                <h4 class="font-bold text-xl text-gray-900 dark:text-white mb-2">Dibuat dengan Tangan</h4>
                                <p class="text-gray-500 dark:text-gray-400 font-light leading-relaxed">Diproses secara manual tanpa campur tangan mesin pabrik massal.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-5">
                            <div class="mt-1.5 flex-shrink-0"><div class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></div></div>
                            <div>
                                <h4 class="font-bold text-xl text-gray-900 dark:text-white mb-2">Kualitas Terjaga</h4>
                                <p class="text-gray-500 dark:text-gray-400 font-light leading-relaxed">Material alam pilihan terbaik yang telah melalui tahap sortir ketat.</p>
                            </div>
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
        <section class="py-20 lg:py-28 bg-white dark:bg-gray-900 border-t border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
                    <div class="max-w-2xl">
                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">Galeri Inspirasi</h2>
                        <p class="text-lg text-gray-500 dark:text-gray-400">Estetika kerajinan tangan lokal yang diabadikan melalui lensa visual.</p>
                    </div>
                </div>

                <div v-if="galleries.length" class="flex overflow-x-auto snap-x snap-mandatory hide-scrollbar gap-6 md:gap-8 pb-8 -mx-4 px-4">
                    <template v-for="(chunk, ci) in galleryChunks" :key="ci">
                        <div class="flex-none w-[90vw] md:w-[80vw] lg:w-[65vw] snap-center">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 h-full">
                                <div v-for="(gallery, gi) in chunk" :key="gallery.id"
                                    :class="gi === 0 ? 'md:col-span-2 h-[350px] md:h-[500px]' : 'md:col-span-1 h-[350px] md:h-[500px] hidden md:block'"
                                    class="rounded-2xl overflow-hidden shadow-lg group relative bg-gray-200 dark:bg-gray-800">
                                    <img :src="`/storage/${gallery.image_url}`" :alt="gallery.title" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000" />
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-6 md:p-8">
                                        <span v-if="gallery.label" class="text-amber-400 font-bold text-xs tracking-wider uppercase mb-1">{{ gallery.label }}</span>
                                        <span v-if="gallery.title" class="text-white font-serif font-bold" :class="gi === 0 ? 'text-2xl md:text-3xl' : 'text-lg md:text-xl'">{{ gallery.title }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div v-else class="text-center py-20 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-dashed border-gray-200 dark:border-gray-800">
                    <p class="text-gray-500 dark:text-gray-400">Belum ada foto galeri inspirasi.</p>
                </div>
            </div>
        </section>

        <!-- Video Storytelling -->
        <section class="py-20 lg:py-28 bg-gray-50 dark:bg-gray-950">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-12">
                    <h2 class="text-3xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-6">Melihat Proses Kerajinan</h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400">Saksikan secara langsung dedikasi pengrajin lokal kami.</p>
                </div>
                <div class="aspect-video w-full rounded-3xl overflow-hidden shadow-2xl relative bg-black ring-1 ring-gray-200 dark:ring-gray-800">
                    <iframe class="w-full h-full absolute inset-0" :src="`${videoUrl}?rel=0&modestbranding=1`" title="Proses Pembuatan Anyaman" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        </section>

        <!-- Product Catalog -->
        <section id="katalog" class="py-24 lg:py-32 bg-gray-50 dark:bg-[#0a0a0a] border-t border-gray-200 dark:border-[#1a1a1a]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                    <div class="text-left">
                        <span class="text-amber-600 dark:text-amber-500 font-medium tracking-[0.3em] uppercase text-[10px] md:text-xs mb-2 block">Masterpiece</span>
                        <h2 class="text-4xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white tracking-wide">Koleksi Eksklusif</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-light mt-2">Pilih dan miliki karya seni fungsional favorit Anda.</p>
                    </div>
                    <Link 
                        :href="route('catalog.shop')" 
                        class="text-amber-600 dark:text-amber-400 font-semibold text-sm flex items-center gap-1.5 hover:gap-2.5 transition-all self-start md:self-auto group"
                    >
                        Lihat Semua Produk
                        <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </Link>
                </div>

                <!-- Filter Bar -->
                <div id="katalog-filter" class="bg-white dark:bg-[#111] p-5 rounded-2xl border border-gray-200 dark:border-[#1a1a1a] shadow-sm max-w-4xl mx-auto mb-16 flex flex-col md:flex-row gap-4 items-center justify-between">
                    <!-- Search Input -->
                    <div class="relative w-full md:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            type="search"
                            placeholder="Cari nama produk..."
                            class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/10 outline-none transition-all"
                        />
                    </div>

                    <!-- Options Group -->
                    <div class="flex flex-wrap w-full md:w-auto gap-3 items-center">
                        <!-- Stock Status Selector -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:inline">Stok</span>
                            <select
                                v-model="stockFilter"
                                @change="onStockChange"
                                class="px-4 py-2.5 text-sm rounded-xl border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-amber-500 outline-none transition-all cursor-pointer"
                            >
                                <option value="semua">Semua Stok</option>
                                <option value="tersedia">Tersedia</option>
                                <option value="habis">Habis</option>
                            </select>
                        </div>

                        <!-- Sort Selector -->
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider hidden sm:inline">Urutkan</span>
                            <select
                                v-model="sortBy"
                                class="px-4 py-2.5 text-sm rounded-xl border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-amber-500 outline-none transition-all cursor-pointer"
                            >
                                <option value="default">Default</option>
                                <option value="price-asc">Harga: Terendah</option>
                                <option value="price-desc">Harga: Tertinggi</option>
                                <option value="discount">Diskon Terbesar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 transition-opacity duration-300" :class="{ 'opacity-50': loading }">
                    <div v-if="displayedProducts.length === 0 && !loading" class="col-span-full text-center py-20 text-gray-400 dark:text-gray-600">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-medium">Tidak ada produk ditemukan</p>
                    </div>

                    <div
                        v-for="product in displayedProducts"
                        :key="product.id"
                        class="group flex flex-col bg-transparent overflow-hidden transition-all duration-300"
                    >
                        <!-- Image Container with Aspect Ratio 3:4 -->
                        <div class="relative aspect-[3/4] rounded-2xl overflow-hidden bg-gray-50 dark:bg-gray-900 shadow-sm group-hover:shadow-md transition-shadow duration-300 cursor-pointer" @click="selectedProduct = product">
                            <img v-if="product.image_path" :src="`/storage/${product.image_path}`" :alt="product.name" class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-700" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <!-- Badges -->
                            <div v-if="product.current_stock <= 0" class="absolute top-4 left-4 px-3 py-1 bg-gray-900/90 text-white text-[10px] font-bold uppercase tracking-wider rounded-md backdrop-blur-sm">Habis</div>
                            <div v-else-if="product.discount_percentage > 0" class="absolute top-4 left-4 px-3 py-1 bg-red-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-md">-{{ product.discount_percentage }}%</div>
                            
                            <!-- Quick View Overlay -->
                            <div class="absolute inset-0 bg-black/30 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <span class="px-5 py-2.5 bg-white text-gray-950 text-xs font-bold rounded-full shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 uppercase tracking-wider">
                                    Lihat Detail
                                </span>
                            </div>
                        </div>

                        <!-- Info Section (Editorial Typography) -->
                        <div class="pt-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-[0.2em] block mb-1">Rima Craft Signature</span>
                                <h3 class="font-serif font-semibold text-lg text-gray-900 dark:text-white mb-2 line-clamp-1 hover:text-amber-500 transition-colors cursor-pointer" @click="selectedProduct = product">
                                    {{ product.name }}
                                </h3>
                                <div class="flex items-baseline gap-2 mb-4">
                                    <span class="text-md font-bold text-amber-600 dark:text-amber-400 font-serif">{{ formatPrice(product.pricing?.price ?? product.base_price) }}</span>
                                    <span v-if="product.pricing?.has_discount" class="text-xs text-gray-400 line-through">{{ formatPrice(product.pricing.base_price) }}</span>
                                </div>
                            </div>
                            
                            <!-- Outline Premium Button -->
                            <button
                                @click="addToCart(product)"
                                :disabled="product.current_stock <= 0"
                                class="w-full py-2.5 px-4 border border-amber-500/60 hover:border-amber-500 text-amber-600 dark:text-amber-400 disabled:border-gray-200 disabled:text-gray-400 dark:disabled:border-gray-800 disabled:cursor-not-allowed hover:bg-amber-500 hover:text-white dark:hover:text-gray-950 font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-300"
                            >
                                {{ product.current_stock <= 0 ? 'Stok Habis' : 'Tambah Ke Keranjang' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Product Detail Modal Popup -->
        <div v-if="selectedProduct" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div @click="selectedProduct = null" class="fixed inset-0 bg-black/70 backdrop-blur-sm"></div>
            
            <div class="relative bg-white dark:bg-[#121212] rounded-3xl overflow-hidden max-w-2xl w-full border border-gray-200 dark:border-gray-800 shadow-2xl flex flex-col md:flex-row z-10 max-h-[90vh] overflow-y-auto">
                <!-- Close Button -->
                <button @click="selectedProduct = null" class="absolute top-4 right-4 z-20 w-8 h-8 rounded-full bg-white/80 dark:bg-gray-800/80 text-gray-800 dark:text-white flex items-center justify-center hover:bg-white dark:hover:bg-gray-700 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- Product Image Left/Top -->
                <div class="w-full md:w-1/2 aspect-square md:aspect-auto md:min-h-[400px] bg-gray-50 dark:bg-gray-900 relative">
                    <img v-if="selectedProduct.image_path" :src="`/storage/${selectedProduct.image_path}`" :alt="selectedProduct.name" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>

                <!-- Product Details Right/Bottom -->
                <div class="w-full md:w-1/2 p-6 flex flex-col justify-between">
                    <div>
                        <span class="text-amber-600 dark:text-amber-500 font-semibold tracking-wider text-[10px] uppercase block mb-1">Koleksi Eksklusif Rima Craft</span>
                        <h3 class="text-2xl font-serif font-bold text-gray-900 dark:text-white mb-3">{{ selectedProduct.name }}</h3>
                        
                        <!-- Stock Status Badge -->
                        <div class="flex items-center gap-2 mb-4">
                            <span :class="['px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider', selectedProduct.current_stock > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600' : 'bg-red-50 dark:bg-red-500/10 text-red-600']">
                                {{ selectedProduct.current_stock > 0 ? 'Tersedia' : 'Habis' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Stok: {{ selectedProduct.current_stock }} pcs</span>
                        </div>

                        <!-- Price Section -->
                        <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-1">Harga Terbaik</div>
                            <div class="flex items-baseline gap-2.5">
                                <span class="text-2xl font-black text-amber-650 dark:text-amber-400">{{ formatPrice(selectedProduct.pricing?.price ?? selectedProduct.base_price) }}</span>
                                <span v-if="selectedProduct.pricing?.has_discount" class="text-sm text-gray-400 line-through">{{ formatPrice(selectedProduct.pricing.base_price) }}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-6">
                            <h4 class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Deskripsi Produk</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed font-light whitespace-pre-line max-h-40 overflow-y-auto pr-1">
                                {{ selectedProduct.description || 'Produk premium khas dari Rima Craft. Setiap jengkal anyaman diproduksi secara teliti oleh pengrajin ahli lokal untuk memastikan keindahan dan ketahanan yang optimal.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button
                        @click="addToCart(selectedProduct); selectedProduct = null"
                        :disabled="selectedProduct.current_stock <= 0"
                        class="w-full py-3.5 px-4 bg-amber-500 hover:bg-amber-600 disabled:bg-gray-200 dark:disabled:bg-gray-800 disabled:text-gray-400 disabled:cursor-not-allowed text-white dark:text-gray-950 font-bold rounded-xl text-sm transition-all shadow-md shadow-amber-500/10 hover:shadow-lg hover:shadow-amber-500/20"
                    >
                        {{ selectedProduct.current_stock <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Contact / Lokasi Section -->
        <section id="kontak" class="py-24 lg:py-32 bg-gray-50 dark:bg-[#050505] relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-px bg-gradient-to-r from-transparent via-amber-500/20 to-transparent"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-12 items-center">
                    <div class="lg:col-span-5 order-2 lg:order-1 relative z-10">
                        <span class="text-amber-600 dark:text-amber-500 font-medium tracking-[0.25em] uppercase text-xs mb-6 block">Terhubung Dengan Kami</span>
                        <h2 class="text-4xl md:text-5xl lg:text-6xl font-serif font-bold text-gray-900 dark:text-white mb-12 leading-tight">Kunjungi<br/>Workshop Kami</h2>

                        <div class="space-y-12">
                            <div v-if="settings.address" class="group">
                                <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Alamat Workshop & Studio</h4>
                                <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed font-light max-w-sm">{{ settings.address }}</p>
                            </div>
                            <div v-if="settings.business_hours && settings.business_hours.trim()" class="group">
                                <h4 class="text-[10px] font-bold text-gray-500 dark:text-gray-500 uppercase tracking-[0.2em] mb-4">Jam Operasional</h4>
                                <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed font-light">{{ settings.business_hours }}</p>
                            </div>
                            <div class="w-12 h-px bg-gray-800"></div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-10">
                                <div v-if="waPhone">
                                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">WhatsApp</h4>
                                    <a :href="`https://wa.me/${waPhone}`" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-[#25D366] hover:bg-[#128C7E] text-white font-bold rounded-xl shadow-lg shadow-[#25D366]/20 transition-all hover:scale-105 active:scale-95">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                        Hubungi Kami
                                    </a>
                                </div>
                                <div v-if="settings.email">
                                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Email</h4>
                                    <a :href="`mailto:${settings.email}`" class="text-lg text-gray-700 dark:text-white font-light hover:text-amber-600 dark:hover:text-amber-400 transition-colors">{{ settings.email }}</a>
                                </div>
                                <div v-if="settings.instagram" class="sm:col-span-2">
                                    <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4">Instagram</h4>
                                    <a :href="`https://instagram.com/${settings.instagram}`" target="_blank" class="text-lg text-gray-700 dark:text-white font-light hover:text-amber-600 dark:hover:text-amber-400 transition-colors">@{{ settings.instagram }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-7 order-1 lg:order-2">
                        <div class="relative w-full h-[450px] lg:h-[650px] rounded-[2rem] overflow-hidden ring-1 ring-gray-200 dark:ring-white/5 shadow-2xl group">
                            <iframe v-if="gmapsUrl" :src="gmapsUrl" class="absolute inset-0 w-full h-full border-0" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            <div v-else class="absolute inset-0 bg-gray-100 dark:bg-[#0a0a0a] flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 border border-gray-200 dark:border-gray-800 rounded-[2rem]">
                                <span class="font-serif italic text-xl">Peta Belum Diatur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </PublicLayout>
</template>
