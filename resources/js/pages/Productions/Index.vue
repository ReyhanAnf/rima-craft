<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    productions: Object,
    filters: Object,
});

const toast = useToast();
const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Sukses', detail: flash.success, life: 3000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Error', detail: flash.error, life: 4000 });
    }
}, { deep: true, immediate: true });

const searchQuery = ref(props.filters.search || '');
let searchTimeout = null;

const applySearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('productions.index'), { search: searchQuery.value }, { preserveState: true, replace: true });
    }, 400);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <AdminLayout>
        <Head title="Proses Produksi" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Batch Produksi</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau konversi bahan baku menjadi produk jadi.</p>
                </div>
                <div class="flex w-full md:w-auto gap-3">
                    <div class="relative w-full md:w-64">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </span>
                        <InputText
                            v-model="searchQuery"
                            placeholder="Cari produksi..."
                            class="w-full !pl-9"
                            @input="applySearch"
                        />
                    </div>
                    <Link :href="route('productions.create')">
                        <Button
                            label="Mulai Produksi"
                            icon="pi pi-plus"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </Link>
                </div>
            </div>

            <!-- Table of Productions -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Batch</th>
                                <th scope="col" class="px-6 py-4 font-bold">Hasil Produk Jadi</th>
                                <th scope="col" class="px-6 py-4 font-bold">Bahan Terpakai</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="prod in productions.data" :key="prod.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    #{{ prod.id }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <div v-for="res in prod.results" :key="res.id" class="font-semibold text-xs">
                                            {{ res.product?.name }} ({{ res.qty }} pcs)
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="mat in prod.materials" :key="mat.id" class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-gray-500">
                                            {{ mat.material?.name }}: {{ mat.qty }} {{ mat.material?.unit }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDate(prod.date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        Selesai
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('productions.show', prod.id)">
                                        <Button label="Detail" icon="pi pi-eye" size="small" text rounded />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="productions.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada batch produksi ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="productions.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ productions.from || 0 }} - {{ productions.to || 0 }} dari {{ productions.total }} batch</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in productions.links"
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
