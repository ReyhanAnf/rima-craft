<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

defineProps({
    recentOrders: Array,
    totalOrders: Number,
    pendingOrders: Number,
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
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <AdminLayout>
        <Head title="Customer Portal Dashboard" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Portal Dashboard</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau status pesanan dan transaksi belanja Anda.</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Pesanan</span></template>
                    <template #content><span class="text-2xl font-black text-amber-500">{{ totalOrders }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-xs font-bold uppercase tracking-wider text-gray-400">Sedang Dikirim</span></template>
                    <template #content><span class="text-2xl font-black text-blue-500">{{ pendingOrders }}</span></template>
                </Card>
            </div>

            <!-- Recent Orders -->
            <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Pesanan Terakhir</span></template>
                <template #content>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="px-4 py-3">No. Invoice</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Status Pengiriman</th>
                                    <th class="px-4 py-3 text-right">Total Transaksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-850">
                                <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        {{ order.invoice_number || `#${order.id}` }}
                                    </td>
                                    <td class="px-4 py-3">{{ formatDate(order.date) }}</td>
                                    <td class="px-4 py-3">
                                        <span :class="[
                                            'text-xs px-2 py-0.5 rounded font-bold uppercase',
                                            order.shipping_status === 'shipped' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'
                                        ]">
                                            {{ order.shipping_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">{{ formatCurrency(order.grand_total) }}</td>
                                </tr>
                                <tr v-if="recentOrders.length === 0">
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-400">Belum ada riwayat pesanan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </Card>
        </div>
    </AdminLayout>
</template>
