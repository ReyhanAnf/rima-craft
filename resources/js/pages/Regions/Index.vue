

<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    regions: Object,
    provinces: Array,
    filters: Object,
});

const activeTab = ref(props.filters.type === 'city' && props.filters.tab === 'shipping' ? 'shipping' : 'regions');
const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'province');

let debounceTimer = null;
watch(
    [search, typeFilter, activeTab],
    ([newSearch, newType, newTab]) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            router.get(
                route('regions.index'),
                { 
                    search: newSearch, 
                    type: newTab === 'shipping' ? 'city' : newType,
                    tab: newTab
                },
                { preserveState: true, preserveScroll: true, replace: true }
            );
        }, 300);
    }
);

// Toggle tab
const setTab = (tab) => {
    activeTab.value = tab;
    search.value = '';
    if (tab === 'shipping') {
        typeFilter.value = 'city';
    } else {
        typeFilter.value = 'province';
    }
};

const isModalOpen = ref(false);
const editingRegion = ref(null);
const modalAction = ref('add_province'); // 'add_province', 'add_city', 'edit_region', 'edit_shipping'

const form = useForm({
    type: 'province',
    name: '',
    parent_id: '',
    shipping_cost: 0,
});

const openModal = (action, region = null) => {
    modalAction.value = action;
    editingRegion.value = region;
    form.reset();
    form.clearErrors();

    if (action === 'add_province') {
        form.type = 'province';
    } else if (action === 'add_city') {
        form.type = 'city';
    } else if (action === 'edit_region' && region) {
        form.type = region.type;
        form.name = region.name;
        form.parent_id = region.parent_id || '';
    } else if (action === 'edit_shipping' && region) {
        form.type = region.type;
        form.name = region.name;
        form.parent_id = region.parent_id || '';
        form.shipping_cost = region.shipping_rate ? Number(region.shipping_rate.shipping_cost) : 0;
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editingRegion.value) {
        // For editing shipping, we only update shipping cost. But we can pass all validated fields.
        form.put(route('regions.update', editingRegion.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('regions.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const deleteRegion = (region) => {
    if (confirm(`Apakah Anda yakin ingin menghapus ${region.name}?`)) {
        router.delete(route('regions.destroy', region.id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};
</script>

<template>
    <AdminLayout>
        <Head title="Wilayah & Ongkir" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Wilayah & Ongkir</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data master wilayah dan pengaturan ongkos kirim.</p>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button
                    @click="setTab('regions')"
                    :class="[
                        'px-6 py-3 text-sm font-semibold border-b-2 -mb-px transition-all',
                        activeTab === 'regions'
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 font-bold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Master Wilayah
                </button>
                <button
                    @click="setTab('shipping')"
                    :class="[
                        'px-6 py-3 text-sm font-semibold border-b-2 -mb-px transition-all',
                        activeTab === 'shipping'
                            ? 'border-amber-500 text-amber-600 dark:text-amber-400 font-bold'
                            : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                    ]"
                >
                    Pengaturan Ongkos Kirim
                </button>
            </div>

            <!-- Toolbar & Actions -->
            <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
                <div class="flex flex-1 gap-3 items-center">
                    <!-- Type Filter (Only for Master Wilayah tab) -->
                    <select
                        v-if="activeTab === 'regions'"
                        v-model="typeFilter"
                        class="border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm rounded-lg focus:border-amber-500 focus:ring-amber-500 shadow-sm"
                    >
                        <option value="province">Provinsi</option>
                        <option value="city">Kota/Kabupaten</option>
                    </select>

                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <InputText
                            v-model="search"
                            placeholder="Cari..."
                            class="w-full !pl-9 text-sm"
                        />
                    </div>
                </div>

                <!-- Add Button (Only for Master Wilayah tab) -->
                <div v-if="activeTab === 'regions'" class="flex gap-2">
                    <Button
                        v-if="typeFilter === 'province'"
                        label="Tambah Provinsi"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        @click="openModal('add_province')"
                    />
                    <Button
                        v-else
                        label="Tambah Kota"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        @click="openModal('add_city')"
                    />
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- 1. Master Wilayah Table -->
                <div v-if="activeTab === 'regions'" class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Nama Wilayah</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tipe</th>
                                <th v-if="typeFilter === 'city'" scope="col" class="px-6 py-4 font-bold">Provinsi Induk</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="region in regions.data" :key="region.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ region.name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        {{ region.type === 'province' ? 'Provinsi' : 'Kota/Kabupaten' }}
                                    </span>
                                </td>
                                <td v-if="typeFilter === 'city'" class="px-6 py-4">
                                    {{ region.parent ? region.parent.name : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openModal('edit_region', region)" class="mr-2" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteRegion(region)" />
                                </td>
                            </tr>
                            <tr v-if="regions.data.length === 0">
                                <td :colspan="typeFilter === 'city' ? 4 : 3" class="px-6 py-8 text-center text-gray-400">Tidak ada data wilayah ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2. Pengaturan Ongkir Table -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Kota/Kabupaten</th>
                                <th scope="col" class="px-6 py-4 font-bold">Provinsi</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tarif Ongkos Kirim</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="region in regions.data" :key="region.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ region.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ region.parent ? region.parent.name : '-' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-amber-600 dark:text-amber-400">
                                    {{ formatCurrency(region.shipping_rate ? region.shipping_rate.shipping_cost : 0) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button
                                        label="Atur Ongkir"
                                        icon="pi pi-dollar"
                                        severity="secondary"
                                        text
                                        @click="openModal('edit_shipping', region)"
                                    />
                                </td>
                            </tr>
                            <tr v-if="regions.data.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">Tidak ada data kota/kabupaten ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="regions.links && regions.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ regions.from || 0 }} - {{ regions.to || 0 }} dari {{ regions.total }} data</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in regions.links"
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

        <!-- Form Dialog Modal -->
        <Dialog
            v-model:visible="isModalOpen"
            modal
            :header="
                modalAction === 'add_province' ? 'Tambah Provinsi' :
                modalAction === 'add_city' ? 'Tambah Kota/Kabupaten' :
                modalAction === 'edit_region' ? 'Edit Wilayah' : 'Pengaturan Ongkos Kirim'
            "
            class="w-full max-w-md"
            @hide="closeModal"
        >
            <div class="pt-2">
                <form id="regionForm" @submit.prevent="submitForm" class="space-y-4">
                    <!-- Form fields for adding/editing region -->
                    <template v-if="modalAction === 'add_province'">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Provinsi <span class="text-red-500">*</span></label>
                            <InputText
                                v-model="form.name"
                                required
                                placeholder="Contoh: Jawa Barat"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</div>
                        </div>
                    </template>

                    <template v-else-if="modalAction === 'add_city'">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Provinsi Induk <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.parent_id"
                                required
                                class="w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm rounded-lg focus:border-amber-500 focus:ring-amber-500 shadow-sm"
                            >
                                <option value="" disabled>-- Pilih Provinsi --</option>
                                <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                    {{ prov.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.parent_id" class="text-red-500 text-xs">{{ form.errors.parent_id }}</div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Kota/Kabupaten <span class="text-red-500">*</span></label>
                            <InputText
                                v-model="form.name"
                                required
                                placeholder="Contoh: Kota Bandung"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</div>
                        </div>
                    </template>

                    <template v-else-if="modalAction === 'edit_region'">
                        <div v-if="form.type === 'city'" class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Provinsi Induk <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.parent_id"
                                required
                                class="w-full border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm rounded-lg focus:border-amber-500 focus:ring-amber-500 shadow-sm"
                            >
                                <option value="" disabled>-- Pilih Provinsi --</option>
                                <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                    {{ prov.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.parent_id" class="text-red-500 text-xs">{{ form.errors.parent_id }}</div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Wilayah <span class="text-red-500">*</span></label>
                            <InputText
                                v-model="form.name"
                                required
                                placeholder="Contoh: Jawa Barat / Kota Bandung"
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-xs">{{ form.errors.name }}</div>
                        </div>
                    </template>

                    <template v-else-if="modalAction === 'edit_shipping'">
                        <div class="flex flex-col gap-1.5 mb-2">
                            <label class="text-xs font-semibold text-gray-500">Kota/Kabupaten</label>
                            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ form.name }}</div>
                        </div>
                        
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Ongkos Kirim Default (Rp) <span class="text-red-500">*</span></label>
                            <InputNumber
                                v-model="form.shipping_cost"
                                :min="0"
                                mode="decimal"
                                class="w-full"
                                inputClass="w-full"
                                placeholder="Contoh: 15000"
                                required
                            />
                            <div v-if="form.errors.shipping_cost" class="text-red-500 text-xs">{{ form.errors.shipping_cost }}</div>
                        </div>
                    </template>
                </form>
            </div>
            
            <template #footer>
                <div class="flex justify-end gap-2 border-t border-gray-150 dark:border-gray-800 pt-3">
                    <Button label="Batal" severity="secondary" text @click="closeModal" />
                    <Button
                        type="submit"
                        form="regionForm"
                        :label="modalAction.startsWith('add') ? 'Tambah' : 'Simpan'"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </template>
        </Dialog>
    </AdminLayout>
</template>
