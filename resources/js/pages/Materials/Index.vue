<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

const props = defineProps({
    materials: Object,
    filters: Object,
});

const page = usePage();

// Handle Flash Messages

// Filters State
const searchQuery = ref(props.filters.search || '');
const stockStatus = ref(props.filters.stock_status || '');
const maxStock = ref(props.filters.max_stock || null);

const statusOptions = [
    { label: 'Semua Status Stok', value: '' },
    { label: 'Hampir Habis (Low)', value: 'low' },
    { label: 'Tersedia', value: 'available' },
    { label: 'Habis (0)', value: 'empty' },
];

const unitOptions = [
    { label: 'Kilogram (kg)', value: 'kg' },
    { label: 'Meter (meter)', value: 'meter' },
    { label: 'Pcs (pcs)', value: 'pcs' },
    { label: 'Roll (roll)', value: 'roll' },
];

let filterTimeout = null;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('materials.index'), {
            search: searchQuery.value,
            stock_status: stockStatus.value,
            max_stock: maxStock.value,
        }, { preserveState: true, replace: true });
    }, 400);
};

// Form & Modal State
const isFormOpen = ref(false);
const editingMaterial = ref(null);

const form = useForm({
    name: '',
    unit: 'kg',
    min_stock: 0,
    current_stock: 0,
    last_buy_price: 0,
});

const openCreateModal = () => {
    editingMaterial.value = null;
    form.clearErrors();
    form.reset();
    isFormOpen.value = true;
};

const openEditModal = (material) => {
    editingMaterial.value = material;
    form.clearErrors();
    form.name = material.name;
    form.unit = material.unit;
    form.min_stock = Number(material.min_stock) || 0;
    form.current_stock = Number(material.current_stock) || 0;
    form.last_buy_price = Number(material.last_buy_price) || 0;
    isFormOpen.value = true;
};

const submitForm = () => {
    if (editingMaterial.value) {
        form.put(route('materials.update', editingMaterial.value.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('materials.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteMaterial = (material) => {
    if (confirm(`Hapus bahan baku ${material.name}?`)) {
        router.delete(route('materials.destroy', material.id));
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
        <Head title="Manajemen Bahan Baku" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Inventaris Bahan Baku</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola stok dan harga beli bahan baku.</p>
                </div>
                <Button
                    label="Tambah Bahan"
                    icon="pi pi-plus"
                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold self-start md:self-auto"
                    @click="openCreateModal"
                />
            </div>

            <!-- Filters Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Search -->
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Cari bahan..."
                        class="w-full !pl-9"
                        @input="applyFilters"
                    />
                </div>

                <!-- Stock Status -->
                <Dropdown
                    v-model="stockStatus"
                    :options="statusOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Pilih Status Stok"
                    class="w-full"
                    @change="applyFilters"
                />

                <!-- Max Stock -->
                <div class="w-full">
                    <InputNumber
                        v-model="maxStock"
                        placeholder="Maks. Tingkat Stok..."
                        class="w-full"
                        inputClass="w-full"
                        @input="applyFilters"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Nama Bahan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Harga Beli Terakhir</th>
                                <th scope="col" class="px-6 py-4 font-bold">Minimum Stok</th>
                                <th scope="col" class="px-6 py-4 font-bold">Stok Saat Ini</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="material in materials.data" :key="material.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ material.name }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-700 dark:text-gray-300">
                                    {{ formatCurrency(material.last_buy_price) }} / {{ material.unit }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ material.min_stock }} {{ material.unit }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-xs px-2 py-1 rounded-full font-bold',
                                        material.current_stock <= 0
                                            ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
                                            : material.current_stock <= material.min_stock
                                            ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                            : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                    ]">
                                        {{ material.current_stock }} {{ material.unit }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(material)" class="mr-2" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteMaterial(material)" />
                                </td>
                            </tr>
                            <tr v-if="materials.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Tidak ada bahan baku ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile list view -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div v-for="material in materials.data" :key="material.id" class="p-4 flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ material.name }}</h4>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ formatCurrency(material.last_buy_price) }} / {{ material.unit }}</span>
                                <span :class="[
                                    'text-[10px] font-bold px-1.5 py-0.5 rounded',
                                    material.current_stock <= 0
                                        ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
                                        : material.current_stock <= material.min_stock
                                        ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                        : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                ]">
                                    Stok: {{ material.current_stock }} {{ material.unit }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(material)" />
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteMaterial(material)" />
                        </div>
                    </div>
                    <div v-if="materials.data.length === 0" class="p-6 text-center text-gray-400">Tidak ada bahan baku ditemukan.</div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="materials.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ materials.from || 0 }} - {{ materials.to || 0 }} dari {{ materials.total }} bahan</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in materials.links"
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
                :header="editingMaterial ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'"
                class="w-full max-w-md"
                :contentStyle="{ maxHeight: '65vh', overflowY: 'auto' }"
            >
                <!-- Form Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form id="materialForm" @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Bahan <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Contoh: Rotan Alam, Anyaman..." />
                    </div>

                    <!-- Unit -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Satuan Ukuran <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.unit"
                            :options="unitOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Satuan"
                            class="w-full"
                        />
                    </div>

                    <!-- Prices -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Harga Beli Terakhir (Rp) <span class="text-red-500">*</span></label>
                        <InputNumber v-model="form.last_buy_price" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Current Stock -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Stok Saat Ini <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.current_stock" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                        </div>
                        <!-- Minimum Stock Warning -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Batas Minim Stok <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.min_stock" mode="decimal" required :min="0" class="w-full" inputClass="w-full" />
                        </div>
                    </div>
                </form>

                <template #footer>
                    <div class="flex justify-end gap-2 border-t border-gray-150 dark:border-gray-800 pt-3">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            form="materialForm"
                            :label="editingMaterial ? 'Simpan Perubahan' : 'Tambah Bahan'"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
    </AdminLayout>
</template>
