<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    products: { type: Array, default: () => [] },
    settings: { type: Object, default: () => ({}) },
});

const cart = useCartStore();
const toast = useToastStore();

// Filter & Sort State
const search = ref('');
const stockFilter = ref('semua');
const sortBy = ref('default');
const minPrice = ref(null);
const maxPrice = ref(null);
const loading = ref(false);
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

const displayedProducts = computed(() => {
    let items = [...filteredProducts.value];
    
    // Filter by Min Price
    if (minPrice.value !== null && minPrice.value !== '') {
        items = items.filter(p => {
            const price = p.pricing?.price ?? Number(p.base_price);
            return price >= Number(minPrice.value);
        });
    }
    
    // Filter by Max Price
    if (maxPrice.value !== null && maxPrice.value !== '') {
        items = items.filter(p => {
            const price = p.pricing?.price ?? Number(p.base_price);
            return price <= Number(maxPrice.value);
        });
    }

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
        id: product.id,
        name: product.name,
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
</script>

<template>
    <PublicLayout>
        <Head title="Katalog Produk Rima Craft" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-20 space-y-12">
            <!-- Header Section -->
            <div class="border-b border-gray-100 dark:border-gray-900 pb-8 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <nav class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500 mb-3 font-semibold uppercase tracking-wider">
                        <Link href="/" class="hover:text-amber-500 transition-colors">Beranda</Link>
                        <span>/</span>
                        <span class="text-gray-600 dark:text-gray-300">Katalog</span>
                    </nav>
                    <h1 class="text-3xl md:text-5xl font-serif font-bold text-gray-900 dark:text-white tracking-wide">Katalog Anyaman</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-light mt-2 max-w-xl">Telusuri seluruh koleksi kerajinan anyaman rotan eksklusif Rima Craft dengan berbagai pilihan fungsionalitas dan estetika.</p>
                </div>
            </div>

            <!-- Clean Compact Filter Bar -->
            <div class="border-y border-gray-200 dark:border-gray-800 bg-transparent py-4 flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
                <!-- Search Input -->
                <div class="relative w-full lg:w-72">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="search"
                        placeholder="Cari nama produk..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-md border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:border-amber-500 outline-none transition-all"
                    />
                </div>

                <!-- Complete Filter Options -->
                <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                    <!-- Price Range Inputs -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Harga (Rp)</span>
                        <input
                            v-model.number="minPrice"
                            type="number"
                            placeholder="Min"
                            class="w-20 px-3 py-2 text-xs rounded-md border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none focus:border-amber-500"
                        />
                        <span class="text-gray-300">-</span>
                        <input
                            v-model.number="maxPrice"
                            type="number"
                            placeholder="Max"
                            class="w-20 px-3 py-2 text-xs rounded-md border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none focus:border-amber-500"
                        />
                    </div>

                    <!-- Stock Status Selector -->
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Stok</span>
                        <select
                            v-model="stockFilter"
                            @change="onStockChange"
                            class="px-3 py-2 text-xs rounded-md border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-amber-500 outline-none transition-all cursor-pointer h-[34px]"
                        >
                            <option value="semua">Semua</option>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>

                    <!-- Sort Selector -->
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Urutan</span>
                        <select
                            v-model="sortBy"
                            class="px-3 py-2 text-xs rounded-md border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:border-amber-500 outline-none transition-all cursor-pointer h-[34px]"
                        >
                            <option value="default">Default</option>
                            <option value="price-asc">Harga Terendah</option>
                            <option value="price-desc">Harga Tertinggi</option>
                            <option value="discount">Diskon Terbesar</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8 transition-opacity duration-300" :class="{ 'opacity-50': loading }">
                <div v-if="displayedProducts.length === 0 && !loading" class="col-span-full text-center py-24 text-gray-400 dark:text-gray-600">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="font-medium text-lg">Tidak ada produk ditemukan</p>
                    <p class="text-sm text-gray-450 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
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
    </PublicLayout>
</template>
