<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Chart from 'primevue/chart';
import DatePicker from 'primevue/datepicker';

const props = defineProps({
    range: String,
    startDate: String,
    endDate: String,
    totalSales: Number,
    totalPurchases: Number,
    totalProductionCost: Number,
    grossProfit: Number,
    profitMargin: Number,
    productionBreakdown: Object,
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
    chartData: Object,
    recentSales: Array,
    pendingResellers: { type: Array, default: () => [] },
});

const currentTab = ref('general');

// Trend Chart
const chartJsData = computed(() => {
    const categories = props.chartData?.categories || [];
    const sales = props.chartData?.sales || [];
    const purchases = props.chartData?.purchases || [];

    return {
        labels: categories,
        datasets: [
            {
                label: 'Penjualan',
                data: sales,
                borderColor: '#f59e0b', // amber-500
                borderWidth: 3,
                pointBackgroundColor: '#f59e0b',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return 'rgba(245, 158, 11, 0.05)';
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
                    gradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');
                    return gradient;
                },
            },
            {
                label: 'Belanja Bahan',
                data: purchases,
                borderColor: '#3b82f6', // blue-500
                borderWidth: 3,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const { ctx, chartArea } = chart;
                    if (!chartArea) return 'rgba(59, 130, 246, 0.05)';
                    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
                    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');
                    return gradient;
                },
            }
        ]
    };
});

const chartOptions = computed(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? 'rgba(51, 65, 85, 0.5)' : 'rgba(226, 232, 240, 0.8)';
    const tooltipBg = isDark ? '#0f172a' : '#ffffff';
    const tooltipBorder = isDark ? '#334155' : '#e2e8f0';
    const tooltipText = isDark ? '#f1f5f9' : '#1e293b';

    return {
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                labels: {
                    color: isDark ? '#cbd5e1' : '#334155',
                    boxWidth: 12,
                    boxHeight: 12,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: {
                        size: 12,
                        weight: '600',
                        family: 'system-ui, -apple-system, sans-serif'
                    }
                }
            },
            tooltip: {
                backgroundColor: tooltipBg,
                titleColor: tooltipText,
                bodyColor: tooltipText,
                borderColor: tooltipBorder,
                borderWidth: 1,
                padding: 12,
                boxPadding: 6,
                usePointStyle: true,
                pointStyle: 'circle',
                titleFont: {
                    size: 13,
                    weight: 'bold'
                },
                callbacks: {
                    label: (context) => {
                        let label = context.dataset.label || '';
                        if (label) label += ': ';
                        if (context.parsed.y !== null) {
                            label += formatCurrency(context.parsed.y);
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColor,
                    font: { size: 11 }
                },
                grid: { display: false }
            },
            y: {
                ticks: {
                    color: textColor,
                    font: { size: 11 },
                    callback: (value) => formatCompact(value)
                },
                grid: {
                    color: gridColor,
                    drawBorder: false
                }
            }
        },
        interaction: {
            mode: 'index',
            intersect: false
        },
        responsive: true,
        maintainAspectRatio: false
    };
});

// Cost Breakdown Doughnut Chart
const doughnutChartData = computed(() => {
    const breakdown = props.productionBreakdown || { material: 0, labor: 0, overhead: 0 };
    return {
        labels: ['Bahan Baku', 'Upah Tenaga Kerja', 'Biaya Overhead'],
        datasets: [
            {
                data: [breakdown.material, breakdown.labor, breakdown.overhead],
                backgroundColor: ['#f59e0b', '#10b981', '#6366f1'], // amber, emerald, indigo
                borderWidth: 0,
                hoverOffset: 4
            }
        ]
    };
});

const doughnutOptions = computed(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#cbd5e1' : '#334155';

    return {
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: textColor,
                    boxWidth: 10,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11, weight: '500' }
                }
            },
            tooltip: {
                callbacks: {
                    label: (context) => {
                        const val = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                        return ` ${context.label}: ${formatCurrency(val)} (${pct}%)`;
                    }
                }
            }
        },
        cutout: '70%',
        responsive: true,
        maintainAspectRatio: false
    };
});

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

const dates = ref(null);
if (props.startDate || props.endDate) {
    const from = props.startDate ? new Date(props.startDate) : null;
    const to = props.endDate ? new Date(props.endDate) : null;
    dates.value = [from, to];
}

const formatDateString = (date) => {
    if (!date) return '';
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

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

watch(dates, (newVal) => {
    if (newVal && newVal.length === 2) {
        const [from, to] = newVal;
        if (from && to) {
            customStartDate.value = formatDateString(from);
            customEndDate.value = formatDateString(to);
            applyCustomDate();
        }
    }
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const formatCompact = (val) => {
    return new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        compactDisplay: 'short'
    }).format(val || 0);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

// Calculate max spent to set width ratio of progress bars
const maxCustomerSpent = computed(() => {
    if (!props.topCustomers || props.topCustomers.length === 0) return 1;
    return Math.max(...props.topCustomers.map(c => Number(c.total_spent) || 1));
});
const maxSupplierReceived = computed(() => {
    if (!props.topSuppliers || props.topSuppliers.length === 0) return 1;
    return Math.max(...props.topSuppliers.map(s => Number(s.total_received) || 1));
});
const maxProductQty = computed(() => {
    if (!props.topProducts || props.topProducts.length === 0) return 1;
    return Math.max(...props.topProducts.map(p => Number(p.total_sold) || 1));
});
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard Admin" />

        <div class="space-y-6">
            <!-- Header greeting -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-5">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>Halo, {{ $page.props.auth.user.name }}!</span>
                        <span class="text-amber-500 wave-animation">👋</span>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Berikut performa & analisis data operasional keuangan Rima Craft terkini.
                    </p>
                </div>

                <!-- Tab Switcher -->
                <div class="inline-flex bg-gray-150/70 dark:bg-gray-950 p-1.5 rounded-xl border border-gray-200/50 dark:border-gray-800">
                    <button
                        @click="currentTab = 'general'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all duration-200 flex items-center gap-2',
                            currentTab === 'general'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-100 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        <i class="pi pi-th-large text-sm"></i>
                        Ringkasan Umum
                    </button>
                    <button
                        @click="currentTab = 'analytics'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all duration-200 flex items-center gap-2',
                            currentTab === 'analytics'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-100 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        <i class="pi pi-chart-bar text-sm"></i>
                        Detail Analitik & Grafik
                    </button>
                </div>
            </div>

            <!-- Date Filter Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mr-2">Filter Periode:</span>
                    <button
                        v-for="opt in rangeOptions"
                        :key="opt.value"
                        @click="selectedRange = opt.value; filterRange(opt.value)"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-all border',
                            selectedRange === opt.value
                                ? 'bg-amber-500 text-gray-950 border-amber-500 font-bold shadow'
                                : 'bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200/60 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-750'
                        ]"
                    >
                        {{ opt.label }}
                    </button>
                </div>

                <!-- Custom Range Inputs -->
                <div v-if="selectedRange === 'custom'" class="flex items-center gap-2 self-start md:self-auto min-w-[240px]">
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
                <div v-else class="text-[11px] font-medium text-gray-400 dark:text-gray-500">
                    Menampilkan data: <span class="font-bold text-gray-600 dark:text-gray-300">{{ formatDate(props.startDate) }}</span> s/d <span class="font-bold text-gray-600 dark:text-gray-300">{{ formatDate(props.endDate) }}</span>
                </div>
            </div>

            <!-- TAB 1: GENERAL -->
            <div v-if="currentTab === 'general'" class="space-y-6">
                <!-- Financial Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Revenue -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Pendapatan</span>
                            <span class="p-2 bg-emerald-50 dark:bg-emerald-950/40 rounded-lg text-emerald-600 dark:text-emerald-400">
                                <i class="pi pi-arrow-up-right text-xs"></i>
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mt-3">{{ formatCurrency(totalSales) }}</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Kotor hasil penjualan online & offline</p>
                        <div class="absolute bottom-0 left-0 h-1 bg-emerald-500 w-full"></div>
                    </div>

                    <!-- Purchases -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Belanja Bahan</span>
                            <span class="p-2 bg-rose-50 dark:bg-rose-950/40 rounded-lg text-rose-600 dark:text-rose-400">
                                <i class="pi pi-shopping-bag text-xs"></i>
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mt-3">{{ formatCurrency(totalPurchases) }}</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Belanja bahan baku ke supplier</p>
                        <div class="absolute bottom-0 left-0 h-1 bg-rose-500 w-full"></div>
                    </div>

                    <!-- Production Cost -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Biaya Produksi</span>
                            <span class="p-2 bg-blue-50 dark:bg-blue-950/40 rounded-lg text-blue-600 dark:text-blue-400">
                                <i class="pi pi-cog text-xs"></i>
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mt-3">{{ formatCurrency(totalProductionCost) }}</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Bahan terpakai, upah & overhead</p>
                        <div class="absolute bottom-0 left-0 h-1 bg-blue-500 w-full"></div>
                    </div>

                    <!-- Profit -->
                    <div class="bg-amber-50/30 dark:bg-amber-950/10 rounded-xl p-5 border border-amber-200 dark:border-amber-900/40 shadow-sm relative overflow-hidden group hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] font-bold text-amber-800 dark:text-amber-400 uppercase tracking-wider">Laba Kotor</span>
                                <span class="ml-2 text-[9px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 px-1.5 py-0.5 rounded-full">
                                    {{ profitMargin.toFixed(1) }}% GPM
                                </span>
                            </div>
                            <span class="p-2 bg-amber-100/50 dark:bg-amber-950/50 rounded-lg text-amber-600 dark:text-amber-400">
                                <i class="pi pi-chart-line text-xs"></i>
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-amber-600 dark:text-amber-450 mt-3">{{ formatCurrency(grossProfit) }}</h3>
                        <p class="text-xs text-gray-505 dark:text-gray-400 mt-2">Omset - Belanja - HPP Produksi</p>
                        <div class="absolute bottom-0 left-0 h-1 bg-amber-500 w-full"></div>
                    </div>

                    <!-- Cashflow -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm relative overflow-hidden group hover:shadow-md transition duration-300">
                        <div class="flex justify-between items-start">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Arus Kas Aktual</span>
                            <span class="p-2 bg-indigo-50 dark:bg-indigo-950/40 rounded-lg text-indigo-600 dark:text-indigo-400">
                                <i class="pi pi-wallet text-xs"></i>
                            </span>
                        </div>
                        <h3 class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-3">{{ formatCurrency(cashInflow - cashOutflow) }}</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
                            Masuk: {{ formatCompact(cashInflow) }} | Keluar: {{ formatCompact(cashOutflow) }}
                        </p>
                        <div class="absolute bottom-0 left-0 h-1 bg-indigo-500 w-full"></div>
                    </div>
                </div>

                <!-- Grid Details: Receivables, Production, Low Stock -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Balance & Outstanding Credit -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-850 dark:text-gray-100 flex items-center gap-2 mb-4">
                            <i class="pi pi-book text-amber-500"></i>
                            <span>Buku Keuangan & Piutang</span>
                        </h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-3">
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">Saldo Kas</p>
                                    <p class="text-[10px] text-gray-400">Total saldo di seluruh kas terdaftar</p>
                                </div>
                                <span class="font-extrabold text-sm text-gray-800 dark:text-white">{{ formatCurrency(totalKas) }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-3">
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">Piutang Pelanggan</p>
                                    <p class="text-[10px] text-gray-400">Pesanan belum lunas dari pembeli</p>
                                </div>
                                <span class="font-extrabold text-sm text-rose-500">{{ formatCurrency(totalReceivables) }}</span>
                            </div>
                            <div class="flex justify-between items-center pb-1">
                                <div>
                                    <p class="text-xs font-bold text-gray-900 dark:text-white">Utang Dagang</p>
                                    <p class="text-[10px] text-gray-400">Tunggakan belanja ke supplier</p>
                                </div>
                                <span class="font-extrabold text-sm text-amber-500">{{ formatCurrency(totalPayables) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Production Status -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-850 dark:text-gray-100 flex items-center gap-2 mb-4">
                                <i class="pi pi-spin pi-cog text-amber-500"></i>
                                <span>Status Kegiatan Produksi</span>
                            </h3>
                            <div class="grid grid-cols-2 gap-4 mt-6">
                                <div class="text-center p-3 bg-amber-50/50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/20 rounded-xl">
                                    <p class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ pendingProductions }}</p>
                                    <p class="text-[10px] font-bold text-amber-700 dark:text-amber-500 uppercase tracking-wider mt-1">Pending Batch</p>
                                </div>
                                <div class="text-center p-3 bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/20 rounded-xl">
                                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ completedProductions }}</p>
                                    <p class="text-[10px] font-bold text-emerald-700 dark:text-emerald-500 uppercase tracking-wider mt-1">Selesai Batch</p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-4 mt-4 text-center">
                            <Link href="/productions" class="text-xs text-amber-500 hover:underline font-bold">Buka Modul Produksi →</Link>
                        </div>
                    </div>

                    <!-- Inventory Warning Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-855 dark:text-gray-100 flex items-center gap-2 mb-2">
                                <i class="pi pi-exclamation-triangle text-rose-500"></i>
                                <span>Peringatan Stok Bahan Baku</span>
                            </h3>
                            <p class="text-[10px] text-gray-400">Bahan baku yang saat ini berada di bawah batas minimum stok.</p>
                            
                            <div class="mt-4 space-y-2">
                                <div v-if="lowStockMaterialsCount > 0" class="flex items-center gap-3 p-3 bg-rose-50/50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-xl text-rose-700 dark:text-rose-400">
                                    <i class="pi pi-info-circle text-lg"></i>
                                    <div class="text-xs">
                                        Ada <span class="font-bold">{{ lowStockMaterialsCount }} item</span> bahan baku hampir habis! Segera lakukan pemesanan.
                                    </div>
                                </div>
                                <div v-else class="flex items-center gap-3 p-3 bg-emerald-50/50 dark:bg-emerald-950/25 border border-emerald-100 dark:border-emerald-900/30 rounded-xl text-emerald-750 dark:text-emerald-400">
                                    <i class="pi pi-check-circle text-lg"></i>
                                    <div class="text-xs">Stok seluruh bahan baku aman.</div>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-4 mt-4 text-center">
                            <Link href="/materials" class="text-xs text-amber-500 hover:underline font-bold">Detail Bahan & Pembelian →</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: ANALYTICS -->
            <div v-if="currentTab === 'analytics'" class="space-y-6">
                <!-- Advanced Charts Grid (Line + Doughnut side-by-side) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Trends Chart (Line) -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Tren Finansial</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Komparasi total nilai penjualan kotor (omset) dan pengeluaran belanja bahan.</p>
                        </div>
                        <div class="h-80 mt-6">
                            <Chart type="line" :data="chartJsData" :options="chartOptions" class="h-full w-full" />
                        </div>
                    </div>

                    <!-- HPP Cost Breakdown Chart (Doughnut) -->
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Komposisi HPP Produksi</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Proporsi pengeluaran biaya produksi terpakai dalam periode ini.</p>
                        </div>
                        <div class="h-64 mt-6 relative flex items-center justify-center">
                            <Chart type="doughnut" :data="doughnutChartData" :options="doughnutOptions" class="h-full w-full" />
                            <div class="absolute flex flex-col items-center justify-center">
                                <span class="text-[10px] text-gray-400 dark:text-gray-505 uppercase font-bold tracking-wider">Total HPP</span>
                                <span class="text-base font-black text-gray-800 dark:text-white mt-0.5">{{ formatCompact(totalProductionCost) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Sales List & Top Leaderboards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Recent Sales (Left / 2 Columns) -->
                    <div class="lg:col-span-2 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Riwayat Transaksi Penjualan Terbaru</h3>
                            <Link href="/sales" class="text-xs text-amber-500 hover:underline">Semua Penjualan</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600 dark:text-gray-400">
                                <thead>
                                    <tr class="text-gray-400 border-b border-gray-200 dark:border-gray-800 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="pb-3 pl-2">Invoice / Ref</th>
                                        <th class="pb-3">Pelanggan</th>
                                        <th class="pb-3">Tanggal</th>
                                        <th class="pb-3 text-right pr-2">Total Transaksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition duration-150">
                                        <td class="py-3.5 pl-2 font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                            <span :class="['w-2 h-2 rounded-full', sale.id.startsWith('online') ? 'bg-amber-500' : 'bg-indigo-500']"></span>
                                            {{ sale.invoice_number }}
                                        </td>
                                        <td class="py-3.5">{{ sale.contact?.name || 'Umum' }}</td>
                                        <td class="py-3.5 text-gray-550 dark:text-gray-450">{{ formatDate(sale.sale_date) }}</td>
                                        <td class="py-3.5 text-right font-extrabold text-gray-955 dark:text-white pr-2">{{ formatCurrency(sale.total_amount) }}</td>
                                    </tr>
                                    <tr v-if="recentSales.length === 0">
                                        <td colspan="4" class="text-center py-6 text-gray-450">Tidak ada transaksi penjualan dalam periode ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Selling Products Leaderboard -->
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Produk Paling Laris</h3>
                            <div class="space-y-4">
                                <div v-for="(p, idx) in topProducts" :key="idx" class="space-y-1.5">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-semibold text-gray-800 dark:text-gray-200 truncate max-w-xs">{{ idx + 1 }}. {{ p.product?.name || 'Produk' }}</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ p.total_sold }} pcs</span>
                                    </div>
                                    <!-- Progress Bar wrapper -->
                                    <div class="h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" :style="{ width: `${(p.total_sold / maxProductQty) * 100}%` }"></div>
                                    </div>
                                </div>
                                <div v-if="topProducts.length === 0" class="text-center py-6 text-xs text-gray-450 italic">Belum ada produk terjual.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboards Grid (Customers & Suppliers) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Top Customers -->
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Pelanggan Kontributor Utama</h3>
                        <div class="space-y-4">
                            <div v-for="(c, idx) in topCustomers" :key="idx" class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ idx + 1 }}. {{ c.name }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ formatCurrency(c.total_spent) }}</span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full" :style="{ width: `${(c.total_spent / maxCustomerSpent) * 100}%` }"></div>
                                </div>
                            </div>
                            <div v-if="topCustomers.length === 0" class="text-center py-6 text-xs text-gray-450 italic">Belum ada data pelanggan.</div>
                        </div>
                    </div>

                    <!-- Top Suppliers -->
                    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4">Supplier Pengeluaran Terbesar</h3>
                        <div class="space-y-4">
                            <div v-for="(s, idx) in topSuppliers" :key="idx" class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ idx + 1 }}. {{ s.name }}</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ formatCurrency(s.total_received) }}</span>
                                </div>
                                <div class="h-2 w-full bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-rose-500 rounded-full" :style="{ width: `${(s.total_received / maxSupplierReceived) * 100}%` }"></div>
                                </div>
                            </div>
                            <div v-if="topSuppliers.length === 0" class="text-center py-6 text-xs text-gray-450 italic">Belum ada data supplier.</div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Pending Reseller Approvals Widget -->
                <div v-if="pendingResellers.length > 0" class="bg-white dark:bg-gray-900 rounded-xl border border-amber-200 dark:border-amber-900/40 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-amber-100 dark:border-amber-900/30 bg-amber-50/50 dark:bg-amber-950/20">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <div class="flex flex-col">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Persetujuan Reseller Tertunda</h3>
                                <span class="text-xs font-bold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded-full">{{ pendingResellers.length }} menunggu</span>
                            </div>
                        </div>
                        <Link href="/users?role=reseller" class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-semibold">Kelola Semua</Link>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div v-for="user in pendingResellers" :key="user.id" class="flex items-center justify-between px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 font-bold text-xs flex-shrink-0">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0 ml-4">
                                <span class="text-[10px] text-gray-400">{{ new Date(user.created_at).toLocaleDateString('id-ID') }}</span>
                                <button
                                    @click="() => { if(confirm('Verifikasi akun reseller ' + user.name + '?')) router.patch(route('users.verify-reseller', user.id)) }"
                                    class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg transition-colors"
                                >
                                    ✓ Approve
                                </button>
                                <button
                                    @click="() => { if(confirm('Tolak pendaftaran reseller ' + user.name + '?')) router.patch(route('users.reject-reseller', user.id)) }"
                                    class="px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-rose-400 hover:text-rose-600 rounded-lg transition-colors"
                                >
                                    ✕ Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
.wave-animation {
    animation: wave 2s infinite;
    transform-origin: 70% 70%;
    display: inline-block;
}

@keyframes wave {
    0% { transform: rotate( 0.0deg) }
    10% { transform: rotate(14.0deg) }
    20% { transform: rotate(-8.0deg) }
    30% { transform: rotate(14.0deg) }
    40% { transform: rotate(-4.0deg) }
    50% { transform: rotate(10.0deg) }
    60% { transform: rotate( 0.0deg) }
    100% { transform: rotate( 0.0deg) }
}
</style>
