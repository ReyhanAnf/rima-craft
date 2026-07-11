<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';

const props = defineProps({
    wages: Object,
    assignments: Array,
    openJobs: Array,
    summary: Object,
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
}).format(val || 0);

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const joinJob = (job) => {
    router.post(route('artisan.jobs.join', job.id));
};

const statusBadge = (status) => {
    switch (status) {
        case 'open': return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'assigned': return 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
        case 'done': return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        default: return 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Portal Pengrajin" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Portal Pengrajin</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau upah, pekerjaan, dan ringkasan stok produksi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Upah Produksi</p>
                    <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-2">{{ formatCurrency(summary.total_wages) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Upah Pekerjaan</p>
                    <p class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-2">{{ formatCurrency(summary.assigned_job_wages) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Stok Produk</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white mt-2">{{ summary.total_products_stock }}</p>
                </div>
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-5 shadow-sm">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Stok Bahan</p>
                    <p class="text-xl font-black text-gray-900 dark:text-white mt-2">{{ summary.total_materials_stock }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pekerjaan Terbuka</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="job in openJobs" :key="job.id" class="p-4 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ job.title }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ job.description || 'Tidak ada detail pekerjaan.' }}</p>
                                </div>
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400 shrink-0">{{ formatCurrency(job.estimated_wage) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] text-gray-400">Rencana: {{ formatDate(job.work_date) }}</span>
                                <Button label="Ikuti" icon="pi pi-check" size="small" text @click="joinJob(job)" />
                            </div>
                        </div>
                        <div v-if="openJobs.length === 0" class="p-6 text-center text-gray-400 text-sm">Belum ada pekerjaan terbuka.</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pekerjaan Saya</h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="assignment in assignments" :key="assignment.id" class="p-4 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ assignment.job?.title || 'Pekerjaan Dihapus' }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ assignment.notes || assignment.job?.description || 'Tidak ada catatan.' }}</p>
                                </div>
                                <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold uppercase shrink-0', statusBadge(assignment.status)]">{{ assignment.status }}</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-400">Rencana: {{ formatDate(assignment.job?.work_date) }}</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(assignment.assigned_wage || assignment.job?.estimated_wage) }}</span>
                            </div>
                        </div>
                        <div v-if="assignments.length === 0" class="p-6 text-center text-gray-400 text-sm">Belum mengikuti pekerjaan.</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Upah Produksi</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="wage in wages.data" :key="wage.id" class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Produksi #{{ wage.production_id }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ wage.work_description || 'Pekerjaan produksi' }} · {{ formatDate(wage.production?.date) }}</p>
                        </div>
                        <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ formatCurrency(wage.amount) }}</span>
                    </div>
                    <div v-if="wages.data.length === 0" class="p-6 text-center text-gray-400 text-sm">Belum ada upah produksi tercatat.</div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
