<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Pagination from '@/components/Pagination.vue';
import Modal from '@/components/Modal.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    regions: Object,
    provinces: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'province');

watch(
    [search, typeFilter],
    debounce(([newSearch, newType]) => {
        router.get(
            route('regions.index'),
            { search: newSearch, type: newType },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 300)
);

const isModalOpen = ref(false);
const editingRegion = ref(null);

const form = useForm({
    type: 'province',
    name: '',
    parent_id: '',
    shipping_cost: 0,
});

const openModal = (region = null) => {
    editingRegion.value = region;
    if (region) {
        form.type = region.type;
        form.name = region.name;
        form.parent_id = region.parent_id || '';
        form.shipping_cost = region.shipping_cost;
    } else {
        form.reset();
        form.type = typeFilter.value;
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
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);
};
</script>

<template>
    <AdminLayout title="Wilayah & Ongkir">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Wilayah & Ongkir
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    
                    <!-- Toolbar -->
                    <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                            <select
                                v-model="typeFilter"
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >
                                <option value="province">Provinsi</option>
                                <option value="city">Kota/Kabupaten</option>
                            </select>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari wilayah..."
                                class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full sm:w-64"
                            />
                        </div>
                        <button
                            @click="openModal()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Tambah Wilayah
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipe
                                    </th>
                                    <th v-if="typeFilter === 'city'" scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Provinsi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ongkir Default
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="region in regions.data" :key="region.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ region.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                        {{ region.type === 'province' ? 'Provinsi' : 'Kota/Kabupaten' }}
                                    </td>
                                    <td v-if="typeFilter === 'city'" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ region.parent ? region.parent.name : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatCurrency(region.shipping_cost) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="openModal(region)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                        <button @click="deleteRegion(region)" class="text-red-600 hover:text-red-900">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="regions.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        Tidak ada data wilayah ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <Pagination class="mt-6 p-6" :links="regions.links" />
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <Modal :show="isModalOpen" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">
                    {{ editingRegion ? 'Edit Wilayah' : 'Tambah Wilayah' }}
                </h3>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block font-medium text-sm text-gray-700">Tipe Wilayah</label>
                        <select
                            v-model="form.type"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            :disabled="!!editingRegion"
                        >
                            <option value="province">Provinsi</option>
                            <option value="city">Kota/Kabupaten</option>
                        </select>
                        <div v-if="form.errors.type" class="text-red-500 text-xs mt-1">{{ form.errors.type }}</div>
                    </div>

                    <div v-if="form.type === 'city'">
                        <label class="block font-medium text-sm text-gray-700">Provinsi</label>
                        <select
                            v-model="form.parent_id"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option value="">-- Pilih Provinsi --</option>
                            <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                {{ prov.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.parent_id" class="text-red-500 text-xs mt-1">{{ form.errors.parent_id }}</div>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Nama Wilayah</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        />
                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="block font-medium text-sm text-gray-700">Ongkos Kirim Default (Rp)</label>
                        <input
                            v-model="form.shipping_cost"
                            type="number"
                            min="0"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        />
                        <div v-if="form.errors.shipping_cost" class="text-red-500 text-xs mt-1">{{ form.errors.shipping_cost }}</div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            @click="closeModal"
                            class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-indigo-600 border border-transparent rounded-md shadow-sm py-2 px-4 flex justify-center text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AdminLayout>
</template>
