<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const filterStatus = ref(props.filters.status || '');

const statusOptions = [
    { label: 'Semua Status Pesanan', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Dikonfirmasi', value: 'confirmed' },
    { label: 'Diproses', value: 'processing' },
    { label: 'Dikirim', value: 'shipped' },
    { label: 'Selesai', value: 'completed' },
    { label: 'Dibatalkan', value: 'cancelled' },
];

const applyFilters = () => {
    router.get(route('customer.orders'), {
        status: filterStatus.value,
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

const orderStatusConfig = {
    pending:    { label: 'Pending',      classes: 'bg-gray-155 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-700' },
    confirmed:  { label: 'Dikonfirmasi', classes: 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50' },
    processing: { label: 'Diproses',     classes: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/20 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50' },
    shipped:    { label: 'Dikirim',      classes: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/20 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-900/50' },
    completed:  { label: 'Selesai',      classes: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50' },
    cancelled:  { label: 'Dibatalkan',   classes: 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400 border border-red-200 dark:border-red-900/50' },
};

function getOrderStatusBadge(status) {
    return orderStatusConfig[status] ?? { label: status, classes: 'bg-gray-50 text-gray-700 border border-gray-200' };
}

const paymentStatusConfig = {
    unpaid:   { label: 'Belum Bayar', classes: 'bg-red-50 text-red-700 border border-red-200' },
    paid:     { label: 'Lunas',       classes: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    partial:  { label: 'DP',          classes: 'bg-amber-50 text-amber-700 border border-amber-200' },
    refunded: { label: 'Refund',      classes: 'bg-purple-50 text-purple-700 border border-purple-200' },
};

function getPaymentStatusBadge(status) {
    return paymentStatusConfig[status] ?? { label: status, classes: 'bg-gray-50 text-gray-700 border border-gray-200' };
}
</script>

<template>
    <AdminLayout>
        <Head title="Riwayat Pesanan Saya" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Riwayat Pesanan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar transaksi pembelian Anda di toko kami.</p>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                <Dropdown v-model="filterStatus" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full sm:w-64" @change="applyFilters" />
            </div>

            <!-- Table of Orders -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Pesanan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Produk</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Bayar</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Pesanan</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Total Transaksi</th>
                                <th scope="col" class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-black text-gray-900 dark:text-white">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 max-w-[280px]">
                                        <div v-for="(item, idx) in order.items" :key="idx" class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">
                                            {{ item.name }} <span class="text-gray-400 font-normal">({{ item.qty }}x)</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-wider', getPaymentStatusBadge(order.payment_status).classes]">
                                        {{ getPaymentStatusBadge(order.payment_status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-wider', getOrderStatusBadge(order.status).classes]">
                                        {{ getOrderStatusBadge(order.status).label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-amber-600 dark:text-amber-400">
                                    {{ formatCurrency(order.total) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('my-orders.show', order.order_number)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 hover:bg-amber-100 transition active:scale-[0.97]">
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-medium">Tidak ada data pesanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
