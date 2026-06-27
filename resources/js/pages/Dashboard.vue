<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import SelectButton from 'primevue/selectbutton';

const props = defineProps({
    range: String,
    startDate: String,
    endDate: String,
    totalSales: Number,
    totalPurchases: Number,
    grossProfit: Number,
    cashInflow: Number,
    cashOutflow: Number,
    totalKas: Number,
    totalReceivables: Number,
    totalPayables: Number,
    pendingProductions: Number,
    completedProductions: Number,
    lowStockMaterialsCount: Number,
    outstandingSales: Array,
    outstandingPurchases: Array,
    topCustomers: Array,
    topSuppliers: Array,
    topProducts: Array,
    chartData: Array,
    recentSales: Array,
});

const currentTab = ref('general');

const rangeOptions = [
    { label: 'Hari Ini', value: 'today' },
    { label: '7 Hari', value: 'last_7_days' },
    { label: '30 Hari', value: 'last_30_days' },
    { label: 'Bulan Ini', value: 'this_month' },
    { label: 'Tahun Ini', value: 'this_year' },
    { label: 'Kustom', value: 'custom' }
];

const selectedRange = ref(props.range);
const customStartDate = ref(props.startDate ? props.startDate.split('T')[0] : '');
const customEndDate = ref(props.endDate ? props.endDate.split('T')[0] : '');

const filterRange = (val) => {
    if (val !== 'custom') {
        router.get(route('dashboard'), { range: val }, { preserveState: true });
    }
};

const applyCustomDate = () => {
    router.get(route('dashboard'), {
        range: 'custom',
        start_date: customStartDate.value,
        end_date: customEndDate.value
    }, { preserveState: true });
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
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard Admin" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-800 pb-5">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                        Halo, {{ $page.props.auth.user.name }}!
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Berikut performa operasional & keuangan Rima Craft.
                    </p>
                </div>

                <!-- Tab Switcher -->
                <div class="inline-flex bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-800">
                    <button
                        @click="currentTab = 'general'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2',
                            currentTab === 'general'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow-sm border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        Ringkasan Umum
                    </button>
                    <button
                        @click="currentTab = 'analytics'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2',
                            currentTab === 'analytics'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow-sm border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        Detail Analitik & Grafik
                    </button>
                </div>
            </div>

            <!-- Date Filter Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-2">Periode:</span>
                    <button
                        v-for="opt in rangeOptions"
                        :key="opt.value"
                        @click="selectedRange = opt.value; filterRange(opt.value)"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border',
                            selectedRange === opt.value
                                ? 'bg-amber-500 text-gray-950 border-amber-500 font-bold shadow'
                                : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700'
                        ]"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <!-- Custom Range Inputs -->
                <div v-if="selectedRange === 'custom'" class="flex items-center gap-2 self-start md:self-auto">
                    <input type="date" v-model="customStartDate" class="px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" />
                    <span class="text-gray-400 text-xs">-</span>
                    <input type="date" v-model="customEndDate" class="px-2 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" />
                    <Button label="Terapkan" @click="applyCustomDate" size="small" class="!bg-amber-500 !border-amber-500 !text-gray-950 font-bold" />
                </div>
                <div v-else class="text-[11px] font-medium text-gray-400 dark:text-gray-500">
                    Rentang: <span class="font-bold text-gray-600 dark:text-gray-300">{{ formatDate(props.startDate) }}</span> s/d <span class="font-bold text-gray-600 dark:text-gray-300">{{ formatDate(props.endDate) }}</span>
                </div>
            </div>

            <!-- TAB 1: GENERAL -->
            <div v-if="currentTab === 'general'" class="space-y-6">
                <!-- Financial Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Revenue -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border-l-4 border-emerald-500 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Pendapatan (Omset)</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalSales) }}</h3>
                        <div class="text-xs text-gray-400 mt-2">Kotor hasil transaksi Penjualan</div>
                    </div>

                    <!-- Purchases -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border-l-4 border-rose-500 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Pengeluaran (Belanja)</p>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(totalPurchases) }}</h3>
                        <div class="text-xs text-gray-400 mt-2">Belanja bahan & operasional</div>
                    </div>

                    <!-- Profit -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border-l-4 border-teal-500 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Estimasi Laba Kotor</p>
                        <h3 class="text-xl font-bold text-teal-600 dark:text-teal-400 mt-1">{{ formatCurrency(grossProfit) }}</h3>
                        <div class="text-xs text-gray-400 mt-2">Penjualan dikurangi Pembelian</div>
                    </div>

                    <!-- Cashflow -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border-l-4 border-indigo-500 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-md transition">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Arus Kas Aktual</p>
                        <h3 class="text-xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ formatCurrency(cashInflow - cashOutflow) }}</h3>
                        <div class="text-xs text-gray-400 mt-2">Masuk: {{ formatCurrency(cashInflow) }} | Keluar: {{ formatCurrency(cashOutflow) }}</div>
                    </div>
                </div>

                <!-- Grid Details: Receivables, Production, Low Stock -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Saldo & Piutang</template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-xs text-gray-500">Total Kas Terdaftar</span>
                                    <span class="font-semibold">{{ formatCurrency(totalKas) }}</span>
                                </div>
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-xs text-gray-500">Piutang Pelanggan</span>
                                    <span class="font-semibold text-rose-500">{{ formatCurrency(totalReceivables) }}</span>
                                </div>
                                <div class="flex justify-between items-center pb-2">
                                    <span class="text-xs text-gray-500">Utang ke Supplier</span>
                                    <span class="font-semibold text-amber-500">{{ formatCurrency(totalPayables) }}</span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Status Produksi</template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-xs text-gray-500">Sedang Berjalan</span>
                                    <span class="font-semibold text-amber-500">{{ pendingProductions }} Batch</span>
                                </div>
                                <div class="flex justify-between items-center pb-2">
                                    <span class="text-xs text-gray-500">Selesai Diproduksi</span>
                                    <span class="font-semibold text-emerald-500">{{ completedProductions }} Batch</span>
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Stok Bahan Baku</template>
                        <template #content>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center pb-2">
                                    <span class="text-xs text-gray-500">Bahan Hampir Habis</span>
                                    <span class="font-semibold text-red-500">{{ lowStockMaterialsCount }} Item</span>
                                </div>
                                <div v-if="lowStockMaterialsCount > 0">
                                    <Link href="/materials" class="text-xs text-amber-500 hover:underline">Lihat Detail Bahan</Link>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>

            <!-- TAB 2: ANALYTICS (Leaderboard / Recent Sales) -->
            <div v-if="currentTab === 'analytics'" class="space-y-6">
                <!-- Recent Sales List -->
                <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <h3 class="text-lg font-bold mb-4">Penjualan Terbaru</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <th class="pb-2">Invoice</th>
                                    <th class="pb-2">Pelanggan</th>
                                    <th class="pb-2">Tanggal</th>
                                    <th class="pb-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="sale in recentSales" :key="sale.id" class="border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                                    <td class="py-3 font-semibold">{{ sale.invoice_number }}</td>
                                    <td class="py-3">{{ sale.contact?.name || 'Umum' }}</td>
                                    <td class="py-3">{{ formatDate(sale.sale_date) }}</td>
                                    <td class="py-3 text-right font-bold">{{ formatCurrency(sale.total_amount) }}</td>
                                </tr>
                                <tr v-if="recentSales.length === 0">
                                    <td colspan="4" class="text-center py-4 text-gray-400">Tidak ada transaksi penjualan dalam periode ini.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Grid Top Lists -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Pelanggan Teratas</template>
                        <template #content>
                            <ul class="space-y-3">
                                <li v-for="(c, idx) in topCustomers" :key="idx" class="flex justify-between items-center text-xs">
                                    <span>{{ idx + 1 }}. {{ c.name }}</span>
                                    <span class="font-bold">{{ formatCurrency(c.total_spent) }}</span>
                                </li>
                            </ul>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Produk Terlaris</template>
                        <template #content>
                            <ul class="space-y-3">
                                <li v-for="(p, idx) in topProducts" :key="idx" class="flex justify-between items-center text-xs">
                                    <span>{{ idx + 1 }}. {{ p.name }}</span>
                                    <span class="font-bold">{{ p.total_qty }} pcs</span>
                                </li>
                            </ul>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>Supplier Utama</template>
                        <template #content>
                            <ul class="space-y-3">
                                <li v-for="(s, idx) in topSuppliers" :key="idx" class="flex justify-between items-center text-xs">
                                    <span>{{ idx + 1 }}. {{ s.name }}</span>
                                    <span class="font-bold">{{ formatCurrency(s.total_spent) }}</span>
                                </li>
                            </ul>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
