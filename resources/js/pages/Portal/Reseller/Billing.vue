<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    invoices: Object,
    totalBilling: Number,
    totalPaid: Number,
    filters: Object,
});

const filterPayment = ref(props.filters.payment_status || '');

const paymentOptions = [
    { label: 'Semua Status Bayar', value: '' },
    { label: 'Belum Lunas', value: 'unpaid' },
    { label: 'Sebagian (DP)', value: 'partial' },
    { label: 'Lunas', value: 'paid' },
];

const applyFilters = () => {
    router.get(route('reseller.billing'), {
        payment_status: filterPayment.value,
    }, { preserveState: true, replace: true });
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const paymentStatusConfig = {
    unpaid:   { label: 'Belum Bayar', classes: 'bg-red-50 text-red-700 border border-red-200' },
    paid:     { label: 'Lunas',       classes: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    partial:  { label: 'DP',          classes: 'bg-amber-50 text-amber-700 border border-amber-200' },
    refunded: { label: 'Refund',      classes: 'bg-purple-50 text-purple-700 border border-purple-200' },
};

function getPaymentStatusBadge(status) {
    return paymentStatusConfig[status] ?? { label: status, classes: 'bg-gray-50 text-gray-700 border border-gray-250' };
}
</script>

<template>
    <AdminLayout>
        <Head title="Billing & Tagihan Keagenan" />

        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Billing & Tagihan Reseller</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau status faktur tagihan dan riwayat pelunasan dana belanja keagenan.</p>
            </div>

            <!-- Stats billing summary -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Total Pembelanjaan</span></template>
                    <template #content><span class="text-xl font-black text-gray-900 dark:text-white">{{ formatCurrency(totalBilling) }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Total Dana Terbayar</span></template>
                    <template #content><span class="text-xl font-black text-emerald-600">{{ formatCurrency(totalPaid) }}</span></template>
                </Card>
                <Card class="!border !border-red-200 dark:!border-red-950 !bg-red-50/10 dark:!bg-red-950/5 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-red-500">Sisa Piutang Dagang</span></template>
                    <template #content><span class="text-xl font-black text-red-500">{{ formatCurrency(totalBilling - totalPaid) }}</span></template>
                </Card>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <Dropdown v-model="filterPayment" :options="paymentOptions" optionLabel="label" optionValue="value" class="w-full sm:w-64" @change="applyFilters" />
            </div>

            <!-- Table of Invoices -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Pesanan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Bayar</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Dana Terbayar</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Total Tagihan</th>
                                <th scope="col" class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="invoice in invoices.data" :key="invoice.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-black text-gray-900 dark:text-white">
                                    {{ invoice.order_number }}
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ formatDate(invoice.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[10px] px-2.5 py-1 rounded-full font-black uppercase tracking-wider', getPaymentStatusBadge(invoice.payment_status).classes]">
                                        {{ getPaymentStatusBadge(invoice.payment_status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-emerald-600">
                                    {{ formatCurrency((invoice.payments || []).reduce((sum, p) => sum + Number(p.amount), 0)) }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900 dark:text-white">
                                    {{ formatCurrency(invoice.total) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('my-orders.show', invoice.order_number)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 hover:bg-amber-100 transition active:scale-[0.97]">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="invoices.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 font-medium">Tidak ada faktur billing.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
