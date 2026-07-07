<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';

defineProps({
    recentOrders: Array,
    totalOrders: Number,
    pendingOrders: Number,
    totalBilling: Number,
    paidAmount: Number,
    outstandingBalance: Number,
});

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
    pending:    { label: 'Pending',      classes: 'bg-gray-100 text-gray-700 border border-gray-300' },
    confirmed:  { label: 'Dikonfirmasi', classes: 'bg-blue-50 text-blue-700 border border-blue-200' },
    processing: { label: 'Diproses',     classes: 'bg-indigo-50 text-indigo-700 border border-indigo-200' },
    shipped:    { label: 'Dikirim',      classes: 'bg-cyan-50 text-cyan-700 border border-cyan-200' },
    completed:  { label: 'Selesai',      classes: 'bg-emerald-50 text-emerald-700 border border-emerald-200' },
    cancelled:  { label: 'Dibatalkan',   classes: 'bg-red-50 text-red-700 border border-red-200' },
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
        <Head title="Reseller Dashboard" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Portal Reseller / B2B</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau ringkasan tagihan piutang dan pesanan reseller Anda.</p>
            </div>

            <!-- Shop CTA -->
            <Link :href="route('catalog.index')" class="flex items-center justify-between gap-4 p-5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 rounded-2xl shadow-md shadow-amber-500/20 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-gray-950 text-sm">Mulai Order Sekarang</p>
                        <p class="text-xs text-gray-950/70 mt-0.5">Lihat katalog produk dan buat pesanan reseller</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-950/70 group-hover:translate-x-1 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </Link>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 lg:col-span-1 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Total Order</span></template>
                    <template #content><span class="text-xl font-black text-amber-500">{{ totalOrders }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 lg:col-span-1 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Order Pending</span></template>
                    <template #content><span class="text-xl font-black text-amber-600">{{ pendingOrders }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 lg:col-span-1 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Total Belanja</span></template>
                    <template #content><span class="text-sm font-black text-gray-900 dark:text-white">{{ formatCurrency(totalBilling) }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 lg:col-span-1 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Sudah Dibayar</span></template>
                    <template #content><span class="text-sm font-black text-emerald-600">{{ formatCurrency(paidAmount) }}</span></template>
                </Card>
                <Card class="!border !border-red-200 dark:!border-red-950 !bg-red-50/10 dark:!bg-red-950/5 lg:col-span-1 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-red-500">Sisa Tagihan</span></template>
                    <template #content><span class="text-sm font-black text-red-500">{{ formatCurrency(outstandingBalance) }}</span></template>
                </Card>
            </div>

            <!-- Recent Orders -->
            <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold uppercase tracking-wider text-gray-400">10 Pesanan Reseller Terakhir</span>
                        <Link :href="route('reseller.orders')" class="text-xs font-bold text-amber-650 hover:underline">Lihat Semua</Link>
                    </div>
                </template>
                <template #content>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                                <tr>
                                    <th class="px-4 py-3">No. Pesanan</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Pembayaran</th>
                                    <th class="px-4 py-3">Status Pesanan</th>
                                    <th class="px-4 py-3 text-right">Grand Total</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="px-4 py-3 font-black text-gray-900 dark:text-white">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-4 py-3 text-xs font-semibold text-gray-500">{{ formatDate(order.created_at) }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="['text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-wider', getPaymentStatusBadge(order.payment_status).classes]">
                                            {{ getPaymentStatusBadge(order.payment_status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="['text-[10px] px-2 py-0.5 rounded font-black uppercase tracking-wider', getOrderStatusBadge(order.status).classes]">
                                            {{ getOrderStatusBadge(order.status).label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-amber-600 dark:text-amber-400">{{ formatCurrency(order.total) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <Link :href="route('my-orders.show', order.order_number)" class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50 hover:bg-amber-100 text-xs font-bold transition">
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="recentOrders.length === 0">
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400 font-medium">Belum ada riwayat pesanan reseller.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </Card>
        </div>
    </AdminLayout>
</template>
