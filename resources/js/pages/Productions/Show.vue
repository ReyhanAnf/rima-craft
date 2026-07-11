<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

const props = defineProps({
    production: Object,
});

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
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
        <Head :title="`Detail Produksi #${production.id}`" />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Header buttons -->
            <div class="flex justify-between items-center">
                <Link :href="route('productions.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Batch & Catatan -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Products results card -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-855 pb-3">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase tracking-widest">Detail Batch Produksi</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">Batch #{{ production.id }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Tanggal</div>
                                    <div class="font-semibold text-sm mt-0.5">{{ formatDate(production.date) }}</div>
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <!-- Products Produced -->
                            <div class="py-2">
                                <h4 class="text-xs font-bold text-emerald-600 uppercase tracking-wider mb-3">Hasil Produk Jadi</h4>
                                <div class="space-y-3">
                                    <div v-for="res in production.results" :key="res.id" class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2 last:border-0 last:pb-0">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ res.product?.name || 'Produk Dihapus' }}</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ res.qty }} Pcs</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Artisan Wages -->
                            <div v-if="production.artisan_wages?.length" class="mt-6 border-t border-gray-150 dark:border-gray-800 pt-6">
                                <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider mb-3">Breakdown Upah Pengrajin</h4>
                                <div class="space-y-3">
                                    <div v-for="wage in production.artisan_wages" :key="wage.id" class="text-sm border-b border-gray-100 dark:border-gray-800 pb-2 last:border-0 last:pb-0">
                                        <div class="flex justify-between items-start gap-3">
                                            <div>
                                                <div class="font-bold text-gray-900 dark:text-white">{{ wage.artisan?.name || 'Pengrajin Dihapus' }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ wage.work_description || 'Pekerjaan produksi' }}</div>
                                            </div>
                                            <div class="font-bold text-amber-600 dark:text-amber-400 shrink-0">{{ formatCurrency(wage.amount) }}</div>
                                        </div>
                                        <p v-if="wage.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">{{ wage.notes }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Materials Consumed -->
                            <div class="mt-6 border-t border-gray-150 dark:border-gray-800 pt-6">
                                <h4 class="text-xs font-bold text-red-500 uppercase tracking-wider mb-3">Bahan Baku yang Digunakan</h4>
                                <div class="space-y-3">
                                    <div v-for="mat in production.materials" :key="mat.id" class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2 last:border-0 last:pb-0">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ mat.material?.name || 'Bahan Dihapus' }}</div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ mat.qty }} {{ mat.material?.unit }}</div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Notes & Cost Panel -->
                <div class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Rincian Finansial</span></template>
                        <template #content>
                            <div class="space-y-3 text-xs">
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-gray-500">HPP Bahan:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(production.total_material_cost) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-gray-500">Upah Kerja:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(production.labor_cost) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-gray-500">Overhead:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(production.overhead_cost) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-gray-500">Biaya Lainnya:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(production.additional_cost) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-1 font-bold text-sm">
                                    <span class="text-gray-800 dark:text-gray-200">Total Biaya:</span>
                                    <span class="text-amber-500">
                                        {{ formatCurrency(production.grand_total_cost) }}
                                    </span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Catatan Produksi</span></template>
                        <template #content>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed italic">
                                {{ production.notes || 'Tidak ada catatan khusus.' }}
                            </p>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
