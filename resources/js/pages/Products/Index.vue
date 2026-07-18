<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const props = defineProps({
    products: Object,
    filters: Object,
    regions: Array,
});

const page = usePage();

// Handle Flash Messages

// Search & Filtering
const searchQuery = ref(props.filters.search || '');
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('products.index'), { search: searchQuery.value }, { preserveState: true, replace: true });
    }, 400);
};

const deleteProduct = (product) => {
    if (confirm(`Hapus ${product.name}?`)) {
        router.delete(route('products.destroy', product.id));
    }
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <AdminLayout>
        <Head title="Katalog Produk" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Katalog Produk</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data produk jadi Rima Craft.</p>
                </div>
                <div class="flex w-full md:w-auto gap-3">
                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <InputText
                            v-model="searchQuery"
                            placeholder="Cari produk..."
                            class="w-full !pl-9"
                            @input="handleSearch"
                        />
                    </div>
                    <Link :href="route('products.create')">
                        <Button
                            label="Tambah"
                            icon="pi pi-plus"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </Link>
                </div>
            </div>

            <!-- Table of Products -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Produk</th>
                                <th scope="col" class="px-6 py-4 font-bold">Harga Jual Dasar</th>
                                <th scope="col" class="px-6 py-4 font-bold">Stok</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="product.image_path ? `/storage/${product.image_path}` : 'https://placehold.co/100'"
                                            class="w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                                            alt=""
                                        />
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ product.name }}</div>
                                            <div class="text-xs text-gray-400 max-w-xs truncate">{{ product.description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-semibold text-amber-600 dark:text-amber-400">
                                    {{ formatCurrency(product.base_price) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs px-2 py-1 rounded-full font-bold', product.current_stock > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400']">
                                        {{ product.current_stock }} Pcs
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('products.edit', product.id)">
                                        <Button icon="pi pi-pencil" severity="secondary" text rounded class="mr-2" />
                                    </Link>
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteProduct(product)" />
                                </td>
                            </tr>
                            <tr v-if="products.data.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Tidak ada produk ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div v-for="product in products.data" :key="product.id" class="p-4 flex gap-4 items-center">
                        <img
                            :src="product.image_path ? `/storage/${product.image_path}` : 'https://placehold.co/100'"
                            class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                            alt=""
                        />
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ product.name }}</h4>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ product.description }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(product.base_price) }}</span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400">Stok: {{ product.current_stock }} pcs</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <Link :href="route('products.edit', product.id)">
                                <Button icon="pi pi-pencil" severity="secondary" text size="small" />
                            </Link>
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteProduct(product)" />
                        </div>
                    </div>
                    <div v-if="products.data.length === 0" class="p-6 text-center text-gray-400">Tidak ada produk ditemukan.</div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="products.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ products.from || 0 }} - {{ products.to || 0 }} dari {{ products.total }} produk</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in products.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-semibold border transition',
                                link.active
                                    ? 'bg-amber-500 text-gray-950 border-amber-500 font-bold'
                                    : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800',
                                !link.url ? 'opacity-40 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
