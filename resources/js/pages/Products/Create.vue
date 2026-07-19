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
    image: null,
    gallery_images: [],
    video_links: [''],
    variants: [],
    region_prices: [],
    user_prices: [],
});

const mainImagePreview = ref(null);
const galleryPreviews = ref([]);

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

// Image Change Handlers
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Harga Jual Dasar (Rp) <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.base_price" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Stok Awal <span class="text-red-500">*</span></label>
                                <InputNumber v-model="form.current_stock" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                            </div>
                        </div>
                    </div>

                    <!-- Media Section -->
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider pb-1.5 border-b border-gray-100 dark:border-gray-800">2. Media Produk</h3>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Gambar Utama</label>
                            <div class="flex gap-4 items-center">
                                <div v-if="mainImagePreview" class="w-20 h-20 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-50 flex items-center justify-center">
                                    <img :src="mainImagePreview" class="w-full h-full object-cover" alt="Main preview" />
                                </div>
                                <input type="file" accept="image/*" @change="onMainImageChange" class="text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 cursor-pointer" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Tambahkan Foto Galeri</label>
                            <input type="file" multiple accept="image/*" @change="onGalleryChange" class="text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 cursor-pointer" />
                            <div v-if="galleryPreviews.length > 0" class="grid grid-cols-4 gap-2 mt-2">
                                <img v-for="(src, idx) in galleryPreviews" :key="idx" :src="src" class="w-full aspect-square object-cover rounded-xl border border-gray-200 dark:border-gray-700" alt="Gallery preview" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold">Tautan Video (YouTube/Lainnya)</label>
                                <Button label="Tambah Video" icon="pi pi-plus" size="small" text @click="addVideoLink" />
                            </div>
                            <div v-for="(link, idx) in form.video_links" :key="idx" class="flex gap-2">
                                <InputText v-model="form.video_links[idx]" placeholder="https://..." class="flex-1" type="url" />
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
