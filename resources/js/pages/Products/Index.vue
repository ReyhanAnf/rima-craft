<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    products: Object,
    filters: Object,
    regions: Array,
});

const toast = useToast();
const page = usePage();

// Handle Flash Messages
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Sukses', detail: flash.success, life: 3000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Error', detail: flash.error, life: 4000 });
    }
}, { deep: true, immediate: true });

// Search & Filtering
const searchQuery = ref(props.filters.search || '');
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('products.index'), { search: searchQuery.value }, { preserveState: true, replace: true });
    }, 400);
};

// Form and Modal State
const isFormOpen = ref(false);
const editingProduct = ref(null);

const form = useForm({
    _method: 'POST',
    name: '',
    description: '',
    base_price: 0,
    current_stock: 0,
    image: null,
    gallery_images: [],
    video_links: [''],
    variants: [],
    region_prices: [],
});

const mainImagePreview = ref(null);
const galleryPreviews = ref([]);

const openCreateModal = () => {
    editingProduct.value = null;
    form.clearErrors();
    form.reset();
    form.video_links = [''];
    form.variants = [];
    form.region_prices = [];
    mainImagePreview.value = null;
    galleryPreviews.value = [];
    isFormOpen.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    form.clearErrors();
    form.name = product.name;
    form.description = product.description || '';
    form.base_price = Number(product.base_price) || 0;
    form.current_stock = Number(product.current_stock) || 0;
    form.image = null;
    form.gallery_images = [];
    form.video_links = (product.media_assets || [])
        .filter(m => m.type === 'video')
        .map(m => m.url);
    if (form.video_links.length === 0) {
        form.video_links = [''];
    }
    form.variants = (product.variants || []).map(v => ({ label: v.label, price_adj: v.price_adj ?? 0 }));
    form.region_prices = (product.region_prices || []).map(rp => ({
        region_id: rp.region_id,
        base_price: rp.base_price ? Number(rp.base_price) : null,
        reseller_price: rp.reseller_price ? Number(rp.reseller_price) : null,
    }));
    mainImagePreview.value = product.image_path ? `/storage/${product.image_path}` : null;
    galleryPreviews.value = [];
    isFormOpen.value = true;
};

// Dynamic Video Links Inputs
const addVideoLink = () => {
    form.video_links.push('');
};
const removeVideoLink = (idx) => {
    form.video_links.splice(idx, 1);
};

// Variants
const addVariant = () => {
    form.variants.push({ label: '', price_adj: 0 });
};
const removeVariant = (idx) => {
    form.variants.splice(idx, 1);
};

// Region Prices
const addRegionPrice = () => {
    form.region_prices.push({ region_id: '', base_price: null, reseller_price: null });
};
const removeRegionPrice = (idx) => {
    form.region_prices.splice(idx, 1);
};

// Image Upload Previews
const onMainImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        mainImagePreview.value = URL.createObjectURL(file);
    }
};

const onGalleryChange = (e) => {
    const files = Array.from(e.target.files);
    form.gallery_images = files;
    galleryPreviews.value = files.map(file => URL.createObjectURL(file));
};

const submitForm = () => {
    if (editingProduct.value) {
        form._method = 'PUT';
        form.post(route('products.update', editingProduct.value.id), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form._method = 'POST';
        form.post(route('products.store'), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteProduct = (product) => {
    if (confirm(`Hapus ${product.name}?`)) {
        router.delete(route('products.destroy', product.id));
    }
};

const deleteMedia = (index) => {
    if (confirm('Hapus media ini dari galeri?')) {
        router.delete(route('products.media.destroy', { product: editingProduct.value.id, index }), {
            onSuccess: () => {
                if (editingProduct.value && editingProduct.value.media_assets) {
                    editingProduct.value.media_assets.splice(index, 1);
                }
            }
        });
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
        <Toast />

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
                    <Button
                        label="Tambah"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        @click="openCreateModal"
                    />
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
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(product)" class="mr-2" />
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
                            <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(product)" />
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

            <!-- Form Dialog Modal -->
            <Dialog
                v-model:visible="isFormOpen"
                modal
                :header="editingProduct ? 'Edit Produk' : 'Tambah Produk'"
                class="w-full max-w-lg"
                :contentStyle="{ maxHeight: '65vh', overflowY: 'auto' }"
            >
                <!-- Form Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form id="productForm" @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Produk <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Nama produk jadi..." />
                    </div>

                    <!-- Description -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Deskripsi Singkat</label>
                        <Textarea v-model="form.description" rows="3" placeholder="Tuliskan spesifikasi/keterangan produk..." />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Price -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.base_price" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                        </div>
                        <!-- Stock -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Stok Awal <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.current_stock" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                        </div>
                    </div>

                    <!-- Images Section -->
                    <div class="pt-4 border-t border-gray-150 dark:border-gray-800 space-y-4">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Media Produk</h4>

                        <!-- Main Image -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Gambar Utama</label>
                            <div class="flex gap-4 items-center">
                                <img
                                    v-if="mainImagePreview"
                                    :src="mainImagePreview"
                                    class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm"
                                    alt=""
                                />
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="onMainImageChange"
                                    class="text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 cursor-pointer"
                                />
                            </div>
                        </div>

                        <!-- Existing Gallery (Edit mode only) -->
                        <div v-if="editingProduct && editingProduct.media_assets && editingProduct.media_assets.length > 0" class="space-y-1.5">
                            <label class="text-xs font-semibold">Galeri & Media Tersimpan</label>
                            <div class="grid grid-cols-4 gap-2">
                                <div
                                    v-for="(media, idx) in editingProduct.media_assets"
                                    :key="idx"
                                    class="relative group aspect-square bg-gray-50 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
                                >
                                    <img v-if="media.type === 'image'" :src="`/${media.url}`" class="w-full h-full object-cover" alt="" />
                                    <div v-else class="w-full h-full flex flex-col items-center justify-center p-1 text-center text-red-500">
                                        <i class="pi pi-video text-lg mb-1"></i>
                                        <span class="text-[8px] max-w-full truncate">{{ media.url }}</span>
                                    </div>
                                    <button
                                        type="button"
                                        @click="deleteMedia(idx)"
                                        class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded opacity-0 group-hover:opacity-100 transition shadow hover:bg-red-600"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery Upload -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Tambahkan Foto Galeri</label>
                            <input
                                type="file"
                                multiple
                                accept="image/*"
                                @change="onGalleryChange"
                                class="text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 cursor-pointer"
                            />
                            <div v-if="galleryPreviews.length > 0" class="grid grid-cols-4 gap-2 mt-2">
                                <img
                                    v-for="(src, idx) in galleryPreviews"
                                    :key="idx"
                                    :src="src"
                                    class="w-full aspect-square object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                                    alt=""
                                />
                            </div>
                        </div>

                        <!-- Video Links -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold">Tautan Video (YouTube/Lainnya)</label>
                                <Button label="Tambah Video" icon="pi pi-plus" size="small" text @click="addVideoLink" />
                            </div>
                            <div v-for="(link, idx) in form.video_links" :key="idx" class="flex gap-2">
                                <InputText
                                    v-model="form.video_links[idx]"
                                    placeholder="https://..."
                                    class="flex-1"
                                    type="url"
                                />
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    text
                                    @click="removeVideoLink(idx)"
                                    :disabled="form.video_links.length === 1"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Variants Section (Optional) -->
                    <div class="pt-4 border-t border-gray-150 dark:border-gray-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Varian Produk</h4>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Opsional — misal: Ukuran S, Warna Merah, dsb.</p>
                            </div>
                            <Button label="Tambah Varian" icon="pi pi-plus" size="small" text @click="addVariant" />
                        </div>
                        <div v-if="form.variants.length === 0" class="text-xs text-gray-400 dark:text-gray-600 italic py-2 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                            Belum ada varian — produk dijual tanpa pilihan varian.
                        </div>
                        <div v-for="(variant, idx) in form.variants" :key="idx" class="flex gap-2 items-center">
                            <InputText
                                v-model="form.variants[idx].label"
                                placeholder="Nama varian (mis: Ukuran S)"
                                class="flex-1"
                            />
                            <InputNumber
                                v-model="form.variants[idx].price_adj"
                                placeholder="+ Harga (Rp)"
                                mode="decimal"
                                :min="0"
                                class="w-36"
                                inputClass="w-full"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                @click="removeVariant(idx)"
                            />
                        </div>
                    </div>

                    <!-- Region Prices Section -->
                    <div class="pt-4 border-t border-gray-150 dark:border-gray-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Harga Khusus Wilayah</h4>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Opsional — Override harga dasar/reseller di wilayah tertentu.</p>
                            </div>
                            <Button label="Tambah Harga Wilayah" icon="pi pi-plus" size="small" text @click="addRegionPrice" />
                        </div>
                        <div v-if="form.region_prices.length === 0" class="text-xs text-gray-400 dark:text-gray-600 italic py-2 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                            Belum ada harga khusus wilayah.
                        </div>
                        <div v-for="(rp, idx) in form.region_prices" :key="idx" class="flex flex-col md:flex-row gap-2 items-end p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <div class="w-full md:flex-1">
                                <label class="text-xs font-semibold block mb-1">Wilayah (Provinsi)</label>
                                <select
                                    v-model="form.region_prices[idx].region_id"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                    required
                                >
                                    <option value="" disabled>Pilih Provinsi</option>
                                    <option v-for="region in regions" :key="region.id" :value="region.id">{{ region.name }}</option>
                                </select>
                            </div>
                            <div class="w-full md:w-32">
                                <label class="text-xs font-semibold block mb-1">Harga Dasar (Rp)</label>
                                <InputNumber
                                    v-model="form.region_prices[idx].base_price"
                                    placeholder="Opsional"
                                    mode="decimal"
                                    :min="0"
                                    class="w-full"
                                    inputClass="w-full text-sm"
                                />
                            </div>
                            <div class="w-full md:w-32">
                                <label class="text-xs font-semibold block mb-1">Harga Reseller (Rp)</label>
                                <InputNumber
                                    v-model="form.region_prices[idx].reseller_price"
                                    placeholder="Opsional"
                                    mode="decimal"
                                    :min="0"
                                    class="w-full"
                                    inputClass="w-full text-sm"
                                />
                            </div>
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                class="mt-2 md:mt-0 md:ml-1"
                                @click="removeRegionPrice(idx)"
                            />
                        </div>
                    </div>
                </form>

                <template #footer>
                    <div class="flex justify-end gap-2 border-t border-gray-150 dark:border-gray-800 pt-3">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            form="productForm"
                            :label="editingProduct ? 'Simpan Perubahan' : 'Tambah Produk'"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
    </AdminLayout>
</template>
