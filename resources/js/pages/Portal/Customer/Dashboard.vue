<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';

defineProps({
    recentOrders: Array,
    totalOrders: Number,
    pendingOrders: Number,
});

const page = usePage();
const resellerStatus = computed(() => page.props.auth?.reseller_status ?? null);
const isReseller = computed(() => page.props.auth?.roles?.includes('reseller') ?? false);

const resellerStage = computed(() => {
    if (!isReseller.value) return null;
    if (resellerStatus.value === 'verified') return 'verified';
    if (resellerStatus.value === 'rejected') return 'rejected';
    return 'pending'; // null or 'pending'
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
        <Head title="Customer Portal Dashboard" />

        <div class="space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Portal Dashboard</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pantau status pesanan dan transaksi belanja Anda.</p>
            </div>

            <!-- Reseller Status Banner -->
            <div v-if="resellerStage" class="rounded-2xl border overflow-hidden"
                :class="{
                    'border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-950/30': resellerStage === 'pending',
                    'border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/30': resellerStage === 'verified',
                    'border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-950/30': resellerStage === 'rejected',
                }"
            >
                <!-- Progress Steps -->
                <div class="px-5 pt-5 pb-3">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 flex-shrink-0"
                            :class="{ 'text-amber-500': resellerStage === 'pending', 'text-emerald-500': resellerStage === 'verified', 'text-red-500': resellerStage === 'rejected' }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="resellerStage === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            <path v-else-if="resellerStage === 'verified'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="font-black text-sm"
                            :class="{ 'text-amber-800 dark:text-amber-200': resellerStage === 'pending', 'text-emerald-800 dark:text-emerald-200': resellerStage === 'verified', 'text-red-800 dark:text-red-200': resellerStage === 'rejected' }">
                            Status Akun Reseller:
                            <span v-if="resellerStage === 'pending'">Menunggu Verifikasi</span>
                            <span v-else-if="resellerStage === 'verified'">Terverifikasi ✓</span>
                            <span v-else>Ditolak</span>
                        </p>
                    </div>

                    <!-- Step indicators -->
                    <div class="flex items-center gap-1 mb-4">
                        <!-- Step 1: Daftar -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0 bg-emerald-500 text-white">✓</div>
                                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 truncate">Daftar</span>
                            </div>
                        </div>
                        <div class="flex-1 h-0.5 mx-1" :class="resellerStage !== 'pending' ? 'bg-emerald-400' : 'bg-gray-300 dark:bg-gray-700'"></div>
                        <!-- Step 2: Verifikasi Admin -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0"
                                    :class="{
                                        'bg-amber-400 text-gray-950 animate-pulse': resellerStage === 'pending',
                                        'bg-emerald-500 text-white': resellerStage === 'verified',
                                        'bg-red-400 text-white': resellerStage === 'rejected',
                                    }">
                                    <span v-if="resellerStage === 'pending'">2</span>
                                    <span v-else-if="resellerStage === 'verified'">✓</span>
                                    <span v-else>✕</span>
                                </div>
                                <span class="text-xs font-semibold truncate"
                                    :class="{ 'text-amber-700 dark:text-amber-400': resellerStage === 'pending', 'text-emerald-700 dark:text-emerald-400': resellerStage === 'verified', 'text-red-700 dark:text-red-400': resellerStage === 'rejected' }">
                                    Verifikasi Admin
                                </span>
                            </div>
                        </div>
                        <div class="flex-1 h-0.5 mx-1" :class="resellerStage === 'verified' ? 'bg-emerald-400' : 'bg-gray-300 dark:bg-gray-700'"></div>
                        <!-- Step 3: Aktif -->
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-black flex-shrink-0"
                                    :class="resellerStage === 'verified' ? 'bg-emerald-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-400'">
                                    <span v-if="resellerStage === 'verified'">✓</span>
                                    <span v-else>3</span>
                                </div>
                                <span class="text-xs font-semibold truncate"
                                    :class="resellerStage === 'verified' ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500'">
                                    Akun Aktif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Status message -->
                    <p class="text-xs leading-relaxed"
                        :class="{ 'text-amber-700 dark:text-amber-300': resellerStage === 'pending', 'text-emerald-700 dark:text-emerald-300': resellerStage === 'verified', 'text-red-700 dark:text-red-300': resellerStage === 'rejected' }">
                        <span v-if="resellerStage === 'pending'">
                            Pendaftaran reseller Anda sedang ditinjau oleh tim kami. Proses verifikasi biasanya memakan waktu <strong>1–2 hari kerja</strong>. Sementara itu, Anda tetap dapat berbelanja sebagai pelanggan reguler.
                        </span>
                        <span v-else-if="resellerStage === 'verified'">
                            Akun reseller Anda telah diverifikasi. Anda kini mendapatkan harga reseller eksklusif dan fitur B2B lengkap.
                        </span>
                        <span v-else>
                            Pendaftaran reseller Anda ditolak. Silakan hubungi tim kami untuk informasi lebih lanjut atau daftar ulang dengan data yang valid.
                        </span>
                    </p>
                </div>
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
                        <p class="font-black text-gray-950 text-sm">Mulai Belanja Sekarang</p>
                        <p class="text-xs text-gray-950/70 mt-0.5">Lihat katalog produk dan buat pesanan baru</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-950/70 group-hover:translate-x-1 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </Link>

            <!-- Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Total Pesanan</span></template>
                    <template #content><span class="text-2xl font-black text-amber-500">{{ totalOrders }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                    <template #title><span class="text-xs font-black uppercase tracking-wider text-gray-400">Pesanan Pending</span></template>
                    <template #content><span class="text-2xl font-black text-amber-600">{{ pendingOrders }}</span></template>
                </Card>
            </div>

            <!-- Recent Orders -->
            <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                <template #title>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold uppercase tracking-wider text-gray-400">Pesanan Terakhir</span>
                        <Link :href="route('customer.orders')" class="text-xs font-bold text-amber-650 hover:underline">Lihat Semua</Link>
                    </div>
                </template>
                <template #content>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                                <tr>
                                    <th class="px-4 py-3">No. Pesanan</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Status Bayar</th>
                                    <th class="px-4 py-3">Status Pesanan</th>
                                    <th class="px-4 py-3 text-right">Total Transaksi</th>
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
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 font-medium">Belum ada riwayat pesanan.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </Card>
        </div>
    </AdminLayout>
</template>
