<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    sales: Object,
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

// Filter Toggle & State
const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const paymentStatus = ref(props.filters.payment_status || '');
const shippingStatus = ref(props.filters.shipping_status || '');
const minAmount = ref(props.filters.min_amount ? Number(props.filters.min_amount) : null);
const maxAmount = ref(props.filters.max_amount ? Number(props.filters.max_amount) : null);

const paymentOptions = [
    { label: 'Semua Status Pembayaran', value: '' },
    { label: 'Belum Lunas', value: 'unpaid' },
    { label: 'Sebagian', value: 'partial' },
    { label: 'Lunas', value: 'paid' },
];

const shippingOptions = [
    { label: 'Semua Status Pengiriman', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Dikirim', value: 'shipped' },
    { label: 'Diterima', value: 'delivered' },
];

let filterTimeout = null;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('sales.index'), {
            search: searchQuery.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
            payment_status: paymentStatus.value,
            shipping_status: shippingStatus.value,
            min_amount: minAmount.value,
            max_amount: maxAmount.value,
        }, { preserveState: true, replace: true });
    }, 400);
};

const clearFilters = () => {
    searchQuery.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    paymentStatus.value = '';
    shippingStatus.value = '';
    minAmount.value = null;
    maxAmount.value = null;
    applyFilters();
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
        <Head title="Daftar Penjualan" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Penjualan</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau seluruh faktur penjualan produk ke pelanggan.</p>
                </div>
                <div class="flex w-full md:w-auto gap-3">
                    <Button
                        label="Filter"
                        icon="pi pi-filter"
                        severity="secondary"
                        outlined
                        @click="showFilters = !showFilters"
                    />
                    <Link :href="route('sales.create')">
                        <Button
                            label="Catat Penjualan"
                            icon="pi pi-plus"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </Link>
                </div>
            </div>

            <!-- Filters Panel -->
            <div v-show="showFilters" class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Date From -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tanggal Mulai</label>
                        <input type="date" v-model="dateFrom" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" @change="applyFilters" />
                    </div>

                    <!-- Date To -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tanggal Akhir</label>
                        <input type="date" v-model="dateTo" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" @change="applyFilters" />
                    </div>

                    <!-- Payment Status -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Status Pembayaran</label>
                        <Dropdown v-model="paymentStatus" :options="paymentOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                    </div>

                    <!-- Shipping Status -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Status Pengiriman</label>
                        <Dropdown v-model="shippingStatus" :options="shippingOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                    </div>

                    <!-- Min Amount -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Min. Total (Rp)</label>
                        <InputNumber v-model="minAmount" placeholder="0" class="w-full" @input="applyFilters" />
                    </div>

                    <!-- Max Amount -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Max. Total (Rp)</label>
                        <InputNumber v-model="maxAmount" placeholder="Tak Terbatas" class="w-full" @input="applyFilters" />
                    </div>

                    <!-- Search Customer -->
                    <div class="lg:col-span-2 flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Cari Pelanggan</label>
                        <InputText v-model="searchQuery" placeholder="Nama pelanggan..." class="w-full" @input="applyFilters" />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-150 dark:border-gray-800">
                    <Button label="Reset Filter" severity="secondary" size="small" text @click="clearFilters" />
                </div>
            </div>

            <!-- Table of Sales -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Invoice</th>
                                <th scope="col" class="px-6 py-4 font-bold">Pelanggan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Pembayaran</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Pengiriman</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Total Transaksi</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="sale in sales.data" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ sale.invoice_number || `#${sale.id}` }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ sale.customer?.name || sale.customer_name || 'Umum' }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDate(sale.date) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-xs px-2.5 py-1 rounded-full font-bold',
                                        sale.payment_status === 'paid'
                                            ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : sale.payment_status === 'partial'
                                            ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                            : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
                                    ]">
                                        {{ sale.payment_status === 'paid' ? 'Lunas' : sale.payment_status === 'partial' ? 'Sebagian' : 'Belum Lunas' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-xs px-2.5 py-1 rounded-full font-bold',
                                        sale.shipping_status === 'delivered'
                                            ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                            : sale.shipping_status === 'shipped'
                                            ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400'
                                            : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
                                    ]">
                                        {{ sale.shipping_status === 'delivered' ? 'Diterima' : sale.shipping_status === 'shipped' ? 'Dikirim' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(sale.grand_total) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('sales.show', sale.id)">
                                        <Button label="Detail" icon="pi pi-eye" size="small" text rounded />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="sales.data.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">Tidak ada transaksi penjualan ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div v-for="sale in sales.data" :key="sale.id" class="p-4 flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ sale.invoice_number || `#${sale.id}` }}</h4>
                                <span class="text-xs font-semibold text-gray-500">{{ formatDate(sale.date) }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">Pelanggan: {{ sale.customer?.name || sale.customer_name || 'Umum' }}</p>
                            <div class="flex flex-wrap gap-2 items-center mt-2.5">
                                <span :class="[
                                    'text-[10px] px-1.5 py-0.5 rounded font-bold',
                                    sale.payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-650' : 'bg-red-50 text-red-600'
                                ]">
                                    {{ sale.payment_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </span>
                                <span class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(sale.grand_total) }}</span>
                            </div>
                        </div>
                        <Link :href="route('sales.show', sale.id)" class="ml-4">
                            <Button icon="pi pi-chevron-right" severity="secondary" text rounded />
                        </Link>
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="sales.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ sales.from || 0 }} - {{ sales.to || 0 }} dari {{ sales.total }} penjualan</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in sales.links"
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
