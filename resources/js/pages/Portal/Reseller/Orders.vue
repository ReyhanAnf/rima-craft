<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    orders: Object,
    filters: Object,
});

const filterStatus = ref(props.filters.status || '');
const filterPayment = ref(props.filters.payment_status || '');

const statusOptions = [
    { label: 'Semua Status Pengiriman', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Shipped', value: 'shipped' },
    { label: 'Delivered', value: 'delivered' },
];

const paymentOptions = [
    { label: 'Semua Status Bayar', value: '' },
    { label: 'Belum Lunas', value: 'unpaid' },
    { label: 'Sebagian', value: 'partial' },
    { label: 'Lunas', value: 'paid' },
];

const applyFilters = () => {
    router.get(route('reseller.orders'), {
        status: filterStatus.value,
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
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <AdminLayout>
        <Head title="Riwayat Order Reseller" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Riwayat Order Reseller</h2>
                <p class="text-xs text-gray-500 mt-0.5">Daftar seluruh pesanan keagenan / reseller Anda.</p>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col sm:flex-row gap-3">
                <Dropdown v-model="filterStatus" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full sm:w-64" @change="applyFilters" />
                <Dropdown v-model="filterPayment" :options="paymentOptions" optionLabel="label" optionValue="value" class="w-full sm:w-64" @change="applyFilters" />
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Faktur</th>
                                <th scope="col" class="px-6 py-4 font-bold">Produk</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Pembayaran</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Kirim</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Grand Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ order.invoice_number || `#${order.id}` }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-0.5">
                                        <div v-for="item in order.items" :key="item.id" class="text-xs">
                                            {{ item.product?.name }} ({{ item.qty }} pcs)
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDate(order.date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-[10px] px-2 py-0.5 rounded font-bold uppercase',
                                        order.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-650' : 'bg-red-50 text-red-600'
                                    ]">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-[10px] px-2 py-0.5 rounded font-bold uppercase',
                                        order.shipping_status === 'shipped' ? 'bg-blue-50 text-blue-650' : 'bg-amber-50 text-amber-600'
                                    ]">
                                        {{ order.shipping_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(order.grand_total) }}
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada riwayat pesanan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
