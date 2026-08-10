<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Message from 'primevue/message';

const props = defineProps({
    regions: Array,
    resellers: Array,
});

const form = useForm({
    name: '',
    description: '',
    base_price: 0,
    current_stock: 0,
    weight: 1000,
    image: null,
    gallery_images: [],
    video_links: [''],
    variants: [],
    region_prices: [],
    user_prices: [],
});

// Image Upload State
const mainImageInput = ref(null);
const galleryInput = ref(null);
const mainImagePreview = ref(null);
const galleryFiles = ref([]);
const galleryPreviews = ref([]);
const activeLightboxUrl = ref(null);
const isMainDragover = ref(false);
const isGalleryDragover = ref(false);

const triggerMainInput = () => {
    mainImageInput.value?.click();
};

const triggerGalleryInput = () => {
    galleryInput.value?.click();
};

const onMainImageChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        setMainImage(file);
    }
};

const onMainDrop = (e) => {
    isMainDragover.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file && file.type.startsWith('image/')) {
        setMainImage(file);
    }
};

const setMainImage = (file) => {
    form.image = file;
    mainImagePreview.value = URL.createObjectURL(file);
};

const removeMainImage = () => {
    form.image = null;
    mainImagePreview.value = null;
    if (mainImageInput.value) {
        mainImageInput.value.value = '';
    }
};

const onGalleryChange = (e) => {
    const files = Array.from(e.target.files || []);
    addGalleryFiles(files);
};

const onGalleryDrop = (e) => {
    isGalleryDragover.value = false;
    const files = Array.from(e.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
    addGalleryFiles(files);
};

const addGalleryFiles = (files) => {
    files.forEach(file => {
        const url = URL.createObjectURL(file);
        galleryFiles.value.push(file);
        galleryPreviews.value.push({ file, url });
    });
    form.gallery_images = galleryFiles.value;
};

const removeGalleryItem = (index) => {
    galleryFiles.value.splice(index, 1);
    galleryPreviews.value.splice(index, 1);
    form.gallery_images = galleryFiles.value;
};

const openLightbox = (url) => {
    activeLightboxUrl.value = url;
};

// Region search
const regionSearch = ref({});
const filteredRegions = (idx) => {
    const q = (regionSearch.value[idx] || '').toLowerCase();
    if (!q) return props.regions;
    return props.regions.filter(r => r.name.toLowerCase().includes(q));
};

// Dynamic Video Links
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

// User (Reseller) Specific Prices
const addUserPrice = () => {
    form.user_prices.push({ user_id: '', price: null });
};
const removeUserPrice = (idx) => {
    form.user_prices.splice(idx, 1);
};

// Already-selected reseller IDs (to prevent duplicates)
const usedResellerIds = computed(() => form.user_prices.map(u => u.user_id).filter(Boolean));
const availableResellers = (currentIdx) => {
    return props.resellers.filter(r => {
        return r.id == form.user_prices[currentIdx]?.user_id || !usedResellerIds.value.includes(r.id);
    });
};

const submitForm = () => {
    form.post(route('products.store'));
};
</script>

<template>
    <AdminLayout>
        <Head title="Tambah Produk Baru" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Link :href="route('products.index')" class="p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-250 dark:border-gray-800 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <i class="pi pi-arrow-left text-sm"></i>
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Produk Baru</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Masukkan data lengkap untuk menambahkan produk jadi baru.</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-6">
                <!-- Form Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Basic Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider pb-1.5 border-b border-gray-100 dark:border-gray-800">1. Informasi Dasar</h3>
                        
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Produk <span class="text-red-500">*</span></label>
                            <InputText v-model="form.name" required placeholder="Nama produk jadi..." />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Deskripsi Singkat</label>
                            <Textarea v-model="form.description" rows="3" placeholder="Tuliskan spesifikasi/keterangan produk..." />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Harga Jual Dasar (Rp) <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.base_price" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Stok Awal <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.current_stock" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Berat (Gram) <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.weight" mode="decimal" required :min="1" class="w-full" inputClass="w-full" placeholder="1000" />
                            </div>
                        </div>
                    </div>

                    <!-- Media Section -->
                    <div class="space-y-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between pb-1.5 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">2. Media Produk</h3>
                                <p class="text-[10px] text-gray-400 mt-0.5">Pilih foto utama, foto galeri tambahan, dan tautan video produk.</p>
                            </div>
                        </div>

                        <!-- Gambar Utama Section -->
                        <div class="space-y-2">
                            <label class="text-xs font-semibold flex items-center gap-1">
                                Gambar Utama <span class="text-red-500">*</span>
                                <span class="text-[10px] text-gray-400 font-normal">(Foto sampul yang muncul pertama di katalog)</span>
                            </label>
                            
                            <input
                                ref="mainImageInput"
                                type="file"
                                accept="image/*"
                                class="hidden"
                                @change="onMainImageChange"
                            />

                            <!-- Main Image Dropzone / Preview -->
                            <div v-if="!mainImagePreview">
                                <div
                                    @click="triggerMainInput"
                                    @dragover.prevent="isMainDragover = true"
                                    @dragleave.prevent="isMainDragover = false"
                                    @drop.prevent="onMainDrop"
                                    :class="[
                                        'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 group flex flex-col items-center justify-center min-h-[150px]',
                                        isMainDragover
                                            ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-500/10'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 bg-gray-50/50 dark:bg-gray-800/40 hover:bg-amber-50/20 dark:hover:bg-amber-500/5'
                                    ]"
                                >
                                    <div class="w-12 h-12 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <i class="pi pi-cloud-upload text-xl"></i>
                                    </div>
                                    <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                        Klik atau seret foto ke sini untuk memilih <span class="text-amber-600 dark:text-amber-400">Gambar Utama</span>
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-1">Format: PNG, JPG, WEBP (Maks 5MB)</p>
                                </div>
                            </div>

                            <div v-else class="flex flex-wrap items-center gap-4">
                                <div class="relative group w-40 h-40 rounded-2xl overflow-hidden border-2 border-amber-500 shadow-md bg-gray-900">
                                    <img :src="mainImagePreview" class="w-full h-full object-cover" alt="Main preview" />
                                    <span class="absolute top-2 left-2 bg-gradient-to-r from-amber-500 to-amber-600 text-gray-950 font-black text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-md shadow">
                                        Gambar Utama
                                    </span>
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                        <button
                                            type="button"
                                            @click="openLightbox(mainImagePreview)"
                                            class="p-2 bg-white/20 hover:bg-white/40 text-white rounded-xl backdrop-blur transition"
                                            title="Perbesar"
                                        >
                                            <i class="pi pi-eye text-sm"></i>
                                        </button>
                                        <button
                                            type="button"
                                            @click="triggerMainInput"
                                            class="p-2 bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold rounded-xl transition"
                                            title="Ganti Foto"
                                        >
                                            <i class="pi pi-pencil text-sm"></i>
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeMainImage"
                                            class="p-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition"
                                            title="Hapus Foto"
                                        >
                                            <i class="pi pi-trash text-sm"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Foto Sampul Terpilih</p>
                                    <p class="text-[10px] text-gray-400">Gambar ini akan dipakai sebagai tampilan utama produk.</p>
                                    <Button label="Ganti Foto" icon="pi pi-refresh" severity="secondary" size="small" outlined @click="triggerMainInput" />
                                </div>
                            </div>
                        </div>

                        <!-- Galeri Foto Tambahan Section -->
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold">Foto Galeri Tambahan (Opsional)</label>
                                <span v-if="galleryPreviews.length > 0" class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">
                                    {{ galleryPreviews.length }} foto terpilih
                                </span>
                            </div>

                            <input
                                ref="galleryInput"
                                type="file"
                                multiple
                                accept="image/*"
                                class="hidden"
                                @change="onGalleryChange"
                            />

                            <!-- Gallery Grid + Add Box -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                <!-- Existing / Selected Gallery Previews -->
                                <div
                                    v-for="(item, idx) in galleryPreviews"
                                    :key="idx"
                                    class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-900"
                                >
                                    <img :src="item.url" class="w-full h-full object-cover" alt="Gallery preview" />
                                    <span class="absolute top-1.5 left-1.5 bg-black/60 text-white text-[9px] font-bold px-1.5 py-0.5 rounded backdrop-blur">
                                        #{{ idx + 1 }}
                                    </span>
                                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="openLightbox(item.url)"
                                            class="p-1.5 bg-white/20 hover:bg-white/40 text-white rounded-lg backdrop-blur transition"
                                            title="Perbesar"
                                        >
                                            <i class="pi pi-eye text-xs"></i>
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeGalleryItem(idx)"
                                            class="p-1.5 bg-red-600 hover:bg-red-500 text-white rounded-lg transition"
                                            title="Hapus"
                                        >
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Add Gallery Dropzone Button -->
                                <div
                                    @click="triggerGalleryInput"
                                    @dragover.prevent="isGalleryDragover = true"
                                    @dragleave.prevent="isGalleryDragover = false"
                                    @drop.prevent="onGalleryDrop"
                                    :class="[
                                        'aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center p-3 text-center cursor-pointer transition-all duration-200 group',
                                        isGalleryDragover
                                            ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-500/10'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 bg-gray-50/50 dark:bg-gray-800/40 hover:bg-amber-50/20 dark:hover:bg-amber-500/5'
                                    ]"
                                >
                                    <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                                        <i class="pi pi-plus text-xs"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-gray-600 dark:text-gray-300">Tambah Foto</span>
                                    <span class="text-[8px] text-gray-400 mt-0.5">Bisa pilih banyak</span>
                                </div>
                            </div>
                        </div>

                        <!-- Lightbox Modal -->
                        <div
                            v-if="activeLightboxUrl"
                            class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
                            @click="activeLightboxUrl = null"
                        >
                            <div class="relative max-w-3xl max-h-[85vh] rounded-2xl overflow-hidden shadow-2xl border border-gray-700" @click.stop>
                                <img :src="activeLightboxUrl" class="max-w-full max-h-[85vh] object-contain bg-black" alt="Full preview" />
                                <button
                                    type="button"
                                    @click="activeLightboxUrl = null"
                                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-black/60 hover:bg-black text-white flex items-center justify-center transition"
                                >
                                    <i class="pi pi-times text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dynamic Video Links -->
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold">Tautan Video (YouTube/Lainnya)</label>
                                <Button label="Tambah Video" icon="pi pi-plus" size="small" text @click="addVideoLink" />
                            </div>
                            <div v-for="(link, idx) in form.video_links" :key="idx" class="flex gap-2">
                                <InputText v-model="form.video_links[idx]" placeholder="https://youtube.com/watch?v=..." class="flex-1" type="url" />
                                <Button icon="pi pi-trash" severity="danger" text @click="removeVideoLink(idx)" :disabled="form.video_links.length === 1" />
                            </div>
                        </div>
                    </div>

                    <!-- Variants Section -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex justify-between items-center pb-1.5 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">3. Varian Produk</h3>
                                <p class="text-[10px] text-gray-400 mt-0.5">Opsional — Misalnya: Ukuran S, Warna Merah, dll. (Harga di bawah merupakan <strong>harga penambahan</strong> dari harga dasar, bukan harga final).</p>
                            </div>
                            <Button label="Tambah Varian" icon="pi pi-plus" size="small" text @click="addVariant" />
                        </div>
                        <div v-if="form.variants.length === 0" class="text-xs text-gray-400 dark:text-gray-600 italic py-6 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            Belum ada varian — produk dijual tanpa pilihan varian.
                        </div>
                        <div v-for="(variant, idx) in form.variants" :key="idx" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center p-3 sm:p-0 border sm:border-0 border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 sm:bg-transparent dark:bg-gray-900/40 dark:sm:bg-transparent">
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-400 sm:hidden block mb-1">Nama Varian <span class="text-red-500">*</span></label>
                                <InputText v-model="form.variants[idx].label" placeholder="Nama varian (mis: Ukuran S)" class="w-full" required />
                            </div>
                            <div class="w-full sm:w-60 flex gap-2 items-center">
                                <div class="flex-1 sm:w-full">
                                    <label class="text-[10px] font-bold text-gray-400 sm:hidden block mb-1">+ Harga Penambahan (Rp)</label>
                                    <InputNumber v-model="form.variants[idx].price_adj" placeholder="+ Harga Penambahan (Rp)" mode="decimal" :min="0" class="w-full" inputClass="w-full text-sm font-bold text-gray-800 dark:text-white" />
                                </div>
                                <Button icon="pi pi-trash" severity="danger" text @click="removeVariant(idx)" class="self-end sm:self-center" />
                            </div>
                        </div>
                    </div>

                    <!-- Region Prices -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex justify-between items-center pb-1.5 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">4. Harga Khusus Wilayah</h3>
                                <p class="text-[10px] text-gray-400 mt-0.5">Opsional — Harga customer/reseller override per Provinsi atau Kabupaten/Kota. Kota/Kab lebih prioritas dari Provinsi.</p>
                            </div>
                            <Button label="Tambah Harga Wilayah" icon="pi pi-plus" size="small" text @click="addRegionPrice" />
                        </div>

                        <div v-if="form.region_prices.length === 0" class="text-xs text-gray-400 dark:text-gray-600 italic py-6 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            Belum ada harga khusus wilayah.
                        </div>

                        <div v-for="(rp, idx) in form.region_prices" :key="idx" class="flex flex-col md:flex-row gap-3 items-end p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/30 dark:bg-gray-900/20">
                            <div class="w-full md:flex-1">
                                <label class="text-xs font-semibold block mb-1">Wilayah <span class="text-red-500">*</span></label>
                                <Dropdown
                                    v-model="form.region_prices[idx].region_id"
                                    :options="regions"
                                    optionLabel="name"
                                    optionValue="id"
                                    :filter="true"
                                    filterPlaceholder="Cari provinsi atau kota/kab..."
                                    placeholder="Pilih Wilayah (Provinsi/Kota)"
                                    class="w-full text-xs"
                                    required
                                />
                            </div>
                            <div class="w-full md:w-40">
                                <label class="text-xs font-semibold block mb-1">Harga Customer (Rp)</label>
                                <InputNumber v-model="form.region_prices[idx].base_price" placeholder="Opsional" mode="decimal" :min="0" class="w-full" inputClass="w-full text-sm" />
                            </div>
                            <div class="w-full md:w-40">
                                <label class="text-xs font-semibold block mb-1">Harga Reseller (Rp)</label>
                                <InputNumber v-model="form.region_prices[idx].reseller_price" placeholder="Opsional" mode="decimal" :min="0" class="w-full" inputClass="w-full text-sm font-bold" />
                            </div>
                            <Button icon="pi pi-trash" severity="danger" text @click="removeRegionPrice(idx)" />
                        </div>
                    </div>

                    <!-- User-Specific Reseller Prices -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex justify-between items-center pb-1.5 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">5. Harga Khusus Reseller Spesifik</h3>
                                <p class="text-[10px] text-gray-400 mt-0.5">Opsional — Harga eksklusif untuk reseller tertentu, lebih prioritas dari harga wilayah mana pun.</p>
                            </div>
                            <Button
                                label="Tambah Reseller"
                                icon="pi pi-plus"
                                size="small"
                                text
                                @click="addUserPrice"
                                :disabled="resellers.length === 0"
                            />
                        </div>

                        <div v-if="resellers.length === 0" class="text-xs text-amber-600 dark:text-amber-400 italic py-4 text-center border border-dashed border-amber-300 dark:border-amber-800 rounded-xl bg-amber-50/30 dark:bg-amber-900/10">
                            <i class="pi pi-info-circle mr-1"></i> Belum ada pengguna dengan role <strong>Reseller</strong>. Tambahkan reseller di menu Pengguna terlebih dahulu.
                        </div>

                        <div v-else-if="form.user_prices.length === 0" class="text-xs text-gray-400 dark:text-gray-600 italic py-6 text-center border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            Belum ada harga khusus reseller individu.
                        </div>

                        <div v-for="(up, idx) in form.user_prices" :key="idx" class="flex flex-col md:flex-row gap-3 items-end p-4 border border-purple-200 dark:border-purple-900/50 rounded-xl bg-purple-50/30 dark:bg-purple-900/10">
                            <div class="w-full md:flex-1">
                                <label class="text-xs font-semibold block mb-1">Reseller <span class="text-red-500">*</span></label>
                                <Dropdown
                                    v-model="form.user_prices[idx].user_id"
                                    :options="availableResellers(idx)"
                                    optionLabel="name"
                                    optionValue="id"
                                    :filter="true"
                                    filterPlaceholder="Cari nama reseller..."
                                    placeholder="Pilih Reseller"
                                    class="w-full text-xs"
                                    required
                                >
                                    <template #option="slotProps">
                                        <div>{{ slotProps.option.name }} — <span class="text-gray-400 text-xs">{{ slotProps.option.email }}</span></div>
                                    </template>
                                </Dropdown>
                            </div>
                            <div class="w-full md:w-48">
                                <label class="text-xs font-semibold block mb-1">Harga Khusus (Rp) <span class="text-red-500">*</span></label>
                                <InputNumber
                                    v-model="form.user_prices[idx].price"
                                    placeholder="Harga eksklusif"
                                    mode="decimal"
                                    :min="0"
                                    class="w-full"
                                    inputClass="w-full text-sm font-bold text-purple-700 dark:text-purple-300"
                                />
                            </div>
                            <Button icon="pi pi-trash" severity="danger" text @click="removeUserPrice(idx)" />
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="flex justify-end gap-3 pt-6 border-t border-gray-100 dark:border-gray-800">
                        <Link :href="route('products.index')" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-sm font-semibold text-gray-700 dark:text-gray-300">
                            Batal
                        </Link>
                        <Button
                            type="submit"
                            label="Simpan Produk"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold px-6"
                        />
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
