<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    orders: Object,
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

// Filter Panel State
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const paymentFilter = ref(props.filters.payment_status || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const dates = ref(null);
if (props.filters.date_from || props.filters.date_to) {
    const from = props.filters.date_from ? new Date(props.filters.date_from) : null;
    const to = props.filters.date_to ? new Date(props.filters.date_to) : null;
    dates.value = [from, to];
}

const formatDateString = (date) => {
    if (!date) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

watch(dates, (newVal) => {
    if (!newVal || newVal.length === 0) {
        dateFrom.value = '';
        dateTo.value = '';
    } else {
        const [from, to] = newVal;
        dateFrom.value = from ? formatDateString(from) : '';
        dateTo.value = to ? formatDateString(to) : '';
    }
    applyFilters();
});

const statusOptions = [
    { label: 'Semua Status Pesanan', value: '' },
    { label: 'Pending', value: 'pending' },
    { label: 'Confirmed', value: 'confirmed' },
    { label: 'Processing', value: 'processing' },
    { label: 'Shipped', value: 'shipped' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const paymentStatusOptions = [
    { label: 'Semua Pembayaran', value: '' },
    { label: 'Belum Bayar (Unpaid)', value: 'unpaid' },
    { label: 'Lunas (Paid)', value: 'paid' },
    { label: 'Refunded', value: 'refunded' },
];

let filterTimeout = null;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('orders.index'), {
            search: searchQuery.value,
            status: statusFilter.value,
            payment_status: paymentFilter.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }, { preserveState: true, replace: true });
    }, 400);
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

const getRequiredAction = (order) => {
    if (order.status === 'pending') {
        return { label: 'Butuh Verifikasi', severity: 'warning', icon: 'pi pi-exclamation-circle' };
    }
    if (order.payment_proof && order.payment_status !== 'paid') {
        return { label: 'Konfirmasi Bayar', severity: 'danger', icon: 'pi pi-wallet' };
    }
    if (order.status === 'confirmed' && order.payment_status === 'paid') {
        return { label: 'Siap Diproses', severity: 'info', icon: 'pi pi-cog' };
    }
    if (order.status === 'processing') {
        return { label: 'Siap Dikirim', severity: 'info', icon: 'pi pi-send' };
    }
    return null;
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Pesanan Online" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pesanan Online (B2C)</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola pesanan masuk langsung dari Landing Page katalog public.</p>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Cari Pesanan</label>
                        <InputText v-model="searchQuery" placeholder="No. order, nama..." class="w-full" @input="applyFilters" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Status Pesanan</label>
                        <Dropdown v-model="statusFilter" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Status Bayar</label>
                        <Dropdown v-model="paymentFilter" :options="paymentStatusOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[10px] font-bold text-gray-400 uppercase">Rentang Tanggal</label>
                        <DatePicker 
                            v-model="dates" 
                            selectionMode="range" 
                            :manualInput="false" 
                            placeholder="Pilih rentang tanggal..." 
                            showIcon 
                            iconDisplay="input" 
                            class="w-full" 
                            dateFormat="dd M yy" 
                        />
                    </div>
                </div>
            </div>

            <!-- Table of Orders -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">

                <!-- Mobile: card list -->
                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-if="orders.data.length === 0" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada pesanan ditemukan.</div>
                    <div
                        v-for="order in orders.data"
                        :key="order.id"
                        class="p-4 space-y-3"
                    >
                        <!-- Row 1: order number + action badge -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-black text-gray-900 dark:text-white">{{ order.order_number }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ formatDate(order.created_at) }}</p>
                            </div>
                            <span v-if="getRequiredAction(order)" :class="[
                                'inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded font-bold uppercase border shrink-0',
                                getRequiredAction(order).severity === 'warning' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 animate-pulse' :
                                getRequiredAction(order).severity === 'danger' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20' :
                                'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                            ]">
                                <i :class="[getRequiredAction(order).icon, 'text-[9px]']"></i>
                                {{ getRequiredAction(order).label }}
                            </span>
                        </div>

                        <!-- Row 2: customer name + phone -->
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ order.customer_name }}</p>
                            <p class="text-[11px] text-gray-400">{{ order.customer_phone }}</p>
                        </div>

                        <!-- Row 3: badges + total + action button -->
                        <div class="flex items-center justify-between gap-2 pt-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span :class="[
                                    'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase',
                                    order.status === 'completed' ? 'bg-emerald-50 text-emerald-700' :
                                    order.status === 'shipped' ? 'bg-blue-50 text-blue-600' :
                                    order.status === 'cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'
                                ]">{{ order.status }}</span>
                                <span :class="[
                                    'text-[10px] px-2 py-0.5 rounded-full font-bold uppercase',
                                    order.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'
                                ]">{{ order.payment_status }}</span>
                                <span class="text-sm font-black text-gray-900 dark:text-white">{{ formatCurrency(order.total) }}</span>
                            </div>
                            <Link :href="route('orders.show', order.id)" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold transition active:scale-[0.97]"
                                :class="getRequiredAction(order)
                                    ? 'bg-amber-500 hover:bg-amber-600 text-gray-950'
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            >
                                <i :class="getRequiredAction(order) ? getRequiredAction(order).icon : 'pi pi-eye'" class="text-[11px]"></i>
                                {{ getRequiredAction(order) ? 'Tindak' : 'Detail' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">No. Pesanan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Pelanggan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Pesanan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Bayar</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tindakan</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Total</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ order.order_number }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ order.customer_name }}</div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ order.customer_phone }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-xs px-2.5 py-1 rounded-full font-bold uppercase',
                                        order.status === 'completed' ? 'bg-emerald-50 text-emerald-650' :
                                        order.status === 'shipped' ? 'bg-blue-50 text-blue-600' :
                                        order.status === 'cancelled' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'
                                    ]">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'text-xs px-2.5 py-1 rounded-full font-bold uppercase',
                                        order.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-650' : 'bg-red-50 text-red-650'
                                    ]">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="getRequiredAction(order)" :class="[
                                        'inline-flex items-center gap-1 text-[11px] px-2 py-0.5 rounded font-bold uppercase border',
                                        getRequiredAction(order).severity === 'warning' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 animate-pulse' :
                                        getRequiredAction(order).severity === 'danger' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20' :
                                        'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20'
                                    ]">
                                        <i :class="[getRequiredAction(order).icon, 'text-[10px]']"></i>
                                        {{ getRequiredAction(order).label }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400 dark:text-gray-600 inline-flex items-center">
                                        <i class="pi pi-check-circle mr-1 text-[10px]"></i> Selesai
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(order.total) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link :href="route('orders.show', order.id)">
                                        <Button
                                            v-if="getRequiredAction(order)"
                                            :label="getRequiredAction(order).label === 'Butuh Verifikasi' ? 'Verifikasi' : getRequiredAction(order).label"
                                            :icon="getRequiredAction(order).icon"
                                            :severity="getRequiredAction(order).severity"
                                            size="small"
                                            class="font-bold shadow-sm"
                                        />
                                        <Button
                                            v-else
                                            label="Detail"
                                            icon="pi pi-eye"
                                            severity="secondary"
                                            text
                                            rounded
                                            size="small"
                                        />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="orders.data.length === 0">
                                <td colspan="8" class="px-6 py-8 text-center text-gray-400">Tidak ada pesanan ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="orders.links.length > 3" class="px-4 md:px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <span class="text-xs text-gray-500">Menampilkan {{ orders.from || 0 }} – {{ orders.to || 0 }} dari {{ orders.total }} pesanan</span>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="link in orders.links"
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
