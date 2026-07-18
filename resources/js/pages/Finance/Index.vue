<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Chart from 'primevue/chart';

const props = defineProps({
    accounts: Array,
    mainAccount: Object,
    ledgers: Object,
    startDate: String,
    endDate: String,
    totalIncome: Number,
    totalExpense: Number,
    netCashFlow: Number,
    breakdowns: Object,
    labelBreakdown: Object,
    monthlyStats: Array,
    filters: Object,
});

const page = usePage();

// Filter Panel State
const filterStartDate = ref(props.filters.start_date || props.startDate);
const filterEndDate = ref(props.filters.end_date || props.endDate);

const dates = ref(null);
if (filterStartDate.value || filterEndDate.value) {
    const from = filterStartDate.value ? new Date(filterStartDate.value) : null;
    const to = filterEndDate.value ? new Date(filterEndDate.value) : null;
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
        filterStartDate.value = '';
        filterEndDate.value = '';
    } else {
        const [from, to] = newVal;
        filterStartDate.value = from ? formatDateString(from) : '';
        filterEndDate.value = to ? formatDateString(to) : '';
    }
    applyFilters();
});

const filterType = ref(props.filters.type || '');

const typeOptions = [
    { label: 'Semua Tipe Transaksi', value: '' },
    { label: 'Uang Masuk (Debit)', value: 'in' },
    { label: 'Uang Keluar (Kredit)', value: 'out' },
];

const applyFilters = () => {
    router.get(route('finance.index'), {
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
        type: filterType.value,
    }, { preserveState: true, replace: true });
};

const printReport = () => {
    const url = route('finance.print', {
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
    });
    window.open(url, '_blank');
};

// Modals State
const isTxModalOpen = ref(false);

const paymentLabelOptions = [
    { label: 'Cash / Tunai', value: 'Cash' },
    { label: 'Bank BCA', value: 'BCA' },
    { label: 'Bank Mandiri', value: 'Mandiri' },
    { label: 'Bank BNI', value: 'BNI' },
    { label: 'Bank BRI', value: 'BRI' },
    { label: 'QRIS / Digital', value: 'QRIS' },
    { label: 'COD (Bayar di Tempat)', value: 'COD' }
];

const txForm = useForm({
    payment_label: 'Cash',
    date: new Date().toISOString().split('T')[0],
    type: 'out',
    amount: 0,
    description: '',
});

const submitTransaction = () => {
    txForm.post(route('finance.transactions.store'), {
        onSuccess: () => {
            isTxModalOpen.value = false;
            txForm.reset();
        }
    });
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

const categoryLabels = {
    sale_income: 'Penjualan',
    purchase_expense: 'Pembelian Bahan',
    production_material: 'HPP Bahan Baku',
    production_labor: 'Upah Tukang',
    production_overhead: 'Overhead',
    manual: 'Manual',
    other: 'Lainnya'
};

const categoryClasses = {
    sale_income: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400',
    purchase_expense: 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400',
    production_material: 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400',
    production_labor: 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400',
    production_overhead: 'bg-purple-50 text-purple-700 dark:bg-purple-950/20 dark:text-purple-400',
    manual: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    other: 'bg-gray-50 text-gray-500 dark:bg-gray-800/40 dark:text-gray-400'
};

// Analytical Chart configurations
const chartData = computed(() => {
    const labels = (props.monthlyStats || []).map(s => {
        const [year, month] = s.month.split('-');
        const date = new Date(Number(year), Number(month) - 1, 1);
        return date.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
    });
    const incomeData = (props.monthlyStats || []).map(s => s.income);
    const expenseData = (props.monthlyStats || []).map(s => s.expense);

    return {
        labels,
        datasets: [
            {
                label: 'Arus Masuk',
                backgroundColor: '#10b981',
                hoverBackgroundColor: '#059669',
                data: incomeData,
                borderRadius: 6
            },
            {
                label: 'Arus Keluar',
                backgroundColor: '#ef4444',
                hoverBackgroundColor: '#dc2626',
                data: expenseData,
                borderRadius: 6
            }
        ]
    };
});

const chartOptions = computed(() => {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#cbd5e1' : '#475569';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

    return {
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    color: textColor,
                    boxWidth: 12,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11, weight: '600' }
                }
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: textColor, font: { size: 10 } }
            },
            y: {
                grid: { color: gridColor },
                ticks: {
                    color: textColor,
                    font: { size: 10 },
                    callback: (value) => {
                        if (value >= 1e6) return 'Rp ' + (value / 1e6) + 'M';
                        if (value >= 1e3) return 'Rp ' + (value / 1e3) + 'K';
                        return 'Rp ' + value;
                    }
                }
            }
        }
    };
});

const getLabelColor = (label) => {
    const l = String(label).toLowerCase();
    if (l.includes('bca')) return 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-800/40';
    if (l.includes('mandiri')) return 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40';
    if (l.includes('qris') || l.includes('digital')) return 'bg-purple-50 text-purple-700 dark:bg-purple-950/20 dark:text-purple-400 border border-purple-200 dark:border-purple-800/40';
    if (l.includes('cod')) return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40';
    return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700';
};
</script>

<template>
    <AdminLayout>
        <Head title="Dasbor Keuangan & Kas" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Keuangan & Kas</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau ringkasan pendapatan, pengeluaran HPP, serta penyebaran alokasi kas.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="Print Laporan" icon="pi pi-print" severity="secondary" outlined @click="printReport" />
                    <Button label="Catat Transaksi" icon="pi pi-plus" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" @click="isTxModalOpen = true" />
                </div>
            </div>

            <!-- Financial Summary widgets with visual improvements -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Saldo Kas Utama Card -->
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 dark:from-amber-600 dark:to-amber-700 text-gray-950 rounded-2xl p-5 shadow-sm relative overflow-hidden group col-span-1 md:col-span-2">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-950/80">Saldo Kas Perusahaan (Kas Utama)</span>
                        <span class="p-2 bg-amber-450/40 rounded-xl text-amber-950">
                            <i class="pi pi-wallet text-sm"></i>
                        </span>
                    </div>
                    <h3 class="text-3xl font-black tracking-tight mt-3 text-gray-950">{{ formatCurrency(mainAccount?.balance ?? 0) }}</h3>
                    <p class="text-xs text-amber-950/70 mt-2 font-medium">Dana terkonsolidasi dari seluruh metode pembayaran.</p>
                    <div class="absolute -right-6 -bottom-6 opacity-10 text-amber-950 pointer-events-none">
                        <i class="pi pi-wallet text-[120px]"></i>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Uang Masuk</span>
                        <span class="p-1.5 bg-emerald-50 dark:bg-emerald-950/30 rounded-lg text-emerald-600 dark:text-emerald-400">
                            <i class="pi pi-arrow-down-left text-xs"></i>
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-3">{{ formatCurrency(totalIncome) }}</h3>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Periode yang difilter</p>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-start">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Uang Keluar</span>
                        <span class="p-1.5 bg-rose-50 dark:bg-rose-950/30 rounded-lg text-rose-600 dark:text-rose-450">
                            <i class="pi pi-arrow-up-right text-xs"></i>
                        </span>
                    </div>
                    <h3 class="text-xl font-extrabold text-red-500 mt-3">{{ formatCurrency(totalExpense) }}</h3>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">Periode yang difilter</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Income vs Expense Bar Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tren Pendapatan & Pengeluaran (6 Bulan Terakhir)</h3>
                    </div>
                    <div class="h-64">
                        <Chart type="bar" :data="chartData" :options="chartOptions" class="h-full" />
                    </div>
                </div>

                <!-- Cash Allocation Breakdown (BCA, Mandiri, COD, Cash) -->
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Penyebaran Alokasi Dana</h3>
                        </div>
                        <div class="space-y-3">
                            <div 
                                v-for="(netAmount, label) in labelBreakdown" 
                                :key="label" 
                                class="flex items-center justify-between p-2.5 rounded-xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800/40"
                            >
                                <div class="flex items-center gap-2">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-bold uppercase', getLabelColor(label)]">
                                        {{ label }}
                                    </span>
                                </div>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ formatCurrency(netAmount) }}</span>
                            </div>
                            <div v-if="Object.keys(labelBreakdown || {}).length === 0" class="text-xs text-gray-400 italic text-center py-6">
                                Belum ada sebaran dana tercatat.
                            </div>
                        </div>
                    </div>
                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 border-t border-gray-100 dark:border-gray-800 pt-2.5">
                        * Alokasi dihitung otomatis berdasarkan history log mutasi pembayaran.
                    </div>
                </div>
            </div>

            <!-- Detailed Breakdowns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Rincian Pendapatan</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Hasil Penjualan Produk (B2C & Offline)</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-450">{{ formatCurrency(breakdowns.sale_income || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm border-t border-gray-100 dark:border-gray-800/60 pt-2">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Pemasukan Manual</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-450">
                                {{ formatCurrency(ledgers.data.filter(l => l.type === 'in' && l.category === 'manual').reduce((sum, l) => sum + Number(l.amount), 0)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Rincian Pengeluaran</h3>
                    <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
                        <div class="flex justify-between items-center col-span-2 sm:col-span-1">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Belanja Bahan Baku:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(breakdowns.purchase_expense || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center col-span-2 sm:col-span-1">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">HPP Bahan Produksi:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(breakdowns.production_material || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center col-span-2 sm:col-span-1 border-t border-gray-100 dark:border-gray-800/40 pt-2">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Upah Tukang (Produksi):</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(breakdowns.production_labor || 0) }}</span>
                        </div>
                        <div class="flex justify-between items-center col-span-2 sm:col-span-1 border-t border-gray-100 dark:border-gray-800/40 pt-2">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Overhead Produksi:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(breakdowns.production_overhead || 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="flex flex-col gap-1 sm:col-span-2">
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
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Tipe Transaksi</label>
                    <Dropdown v-model="filterType" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Sumber / Label</th>
                                <th scope="col" class="px-6 py-4 font-bold">Kategori</th>
                                <th scope="col" class="px-6 py-4 font-bold">Keterangan</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Debit (+)</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Kredit (-)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="ledger in ledgers.data" :key="ledger.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(ledger.date) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider', getLabelColor(ledger.payment_label)]">
                                        {{ ledger.payment_label || 'Cash' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider', categoryClasses[ledger.category] || categoryClasses.other]">
                                        {{ categoryLabels[ledger.category] || ledger.category || 'Lainnya' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                    {{ ledger.description }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                    {{ ledger.type === 'in' ? formatCurrency(ledger.amount) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-red-500">
                                    {{ ledger.type === 'out' ? formatCurrency(ledger.amount) : '-' }}
                                </td>
                            </tr>
                            <tr v-if="ledgers.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada log transaksi kas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div
                        v-for="ledger in ledgers.data"
                        :key="ledger.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] text-gray-400">{{ formatDate(ledger.date) }}</p>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white mt-1 line-clamp-2">
                                    {{ ledger.description }}
                                </h4>
                            </div>
                            <span
                                :class="[
                                    'shrink-0 text-xs font-black',
                                    ledger.type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'
                                ]"
                            >
                                {{ ledger.type === 'in' ? '+' : '-' }} {{ formatCurrency(ledger.amount) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span :class="['px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider', getLabelColor(ledger.payment_label)]">
                                {{ ledger.payment_label || 'Cash' }}
                            </span>
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider', categoryClasses[ledger.category] || categoryClasses.other]">
                                {{ categoryLabels[ledger.category] || ledger.category || 'Lainnya' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="ledgers.data.length === 0" class="p-6 text-center text-gray-400">Tidak ada log transaksi kas.</div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="ledgers.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ ledgers.from || 0 }} - {{ ledgers.to || 0 }} dari {{ ledgers.total }} transaksi</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in ledgers.links"
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

            <!-- Record Transaction Modal -->
            <Dialog v-model:visible="isTxModalOpen" modal header="Catat Transaksi Kas Baru" class="w-full max-w-md">
                <form @submit.prevent="submitTransaction" class="space-y-4 pt-2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Tanggal <span class="text-red-500">*</span></label>
                            <input type="date" v-model="txForm.date" required class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white w-full" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Arah Arus Kas <span class="text-red-500">*</span></label>
                            <Dropdown
                                v-model="txForm.type"
                                :options="[{label: 'Uang Keluar (Kredit)', value: 'out'}, {label: 'Uang Masuk (Debit)', value: 'in'}]"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                                required
                            />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <InputNumber v-model="txForm.amount" :min="1" required placeholder="0" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Sumber / Metode Pembayaran <span class="text-red-500">*</span></label>
                            <Dropdown
                                v-model="txForm.payment_label"
                                :options="paymentLabelOptions"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                                required
                                editable
                                placeholder="Pilih atau ketik..."
                            />
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Keterangan Transaksi <span class="text-red-500">*</span></label>
                        <InputText v-model="txForm.description" required placeholder="Misal: Biaya listrik, Gaji karyawan..." />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isTxModalOpen = false" />
                        <Button type="submit" label="Simpan Transaksi" :loading="txForm.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
