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

                <!-- Notes Panel -->
                <div class="space-y-6">
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
