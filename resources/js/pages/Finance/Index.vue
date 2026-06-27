<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Message from 'primevue/message';

const props = defineProps({
    accounts: Array,
    ledgers: Object,
    startDate: String,
    endDate: String,
    totalIncome: Number,
    totalExpense: Number,
    netCashFlow: Number,
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
const filterAccount = ref(props.filters.account_id ? Number(props.filters.account_id) : '');
const filterStartDate = ref(props.filters.start_date || props.startDate);
const filterEndDate = ref(props.filters.end_date || props.endDate);
const filterType = ref(props.filters.type || '');

const typeOptions = [
    { label: 'Semua Tipe Transaksi', value: '' },
    { label: 'Uang Masuk (Debit)', value: 'in' },
    { label: 'Uang Keluar (Kredit)', value: 'out' },
];

const accountOptions = computed(() => {
    return [
        { name: 'Semua Kas & Bank', id: '' },
        ...props.accounts
    ];
});

const applyFilters = () => {
    router.get(route('finance.index'), {
        account_id: filterAccount.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
        type: filterType.value,
    }, { preserveState: true, replace: true });
};

const printReport = () => {
    const url = route('finance.print', {
        account_id: filterAccount.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
    });
    window.open(url, '_blank');
};

// Modals State
const isAccountModalOpen = ref(false);
const isTxModalOpen = ref(false);

const accountForm = useForm({
    name: '',
    type: 'cash',
    balance: 0,
});

const txForm = useForm({
    account_id: null,
    date: new Date().toISOString().split('T')[0],
    type: 'out',
    amount: 0,
    description: '',
});

const submitAccount = () => {
    accountForm.post(route('finance.accounts.store'), {
        onSuccess: () => {
            isAccountModalOpen.value = false;
            accountForm.reset();
        }
    });
};

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
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Keuangan & Kas" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Keuangan & Kas</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola log arus kas masuk/keluar serta mutasi rekening bank.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button label="Print Laporan" icon="pi pi-print" severity="secondary" outlined @click="printReport" />
                    <Button label="Buat Akun Kas" icon="pi pi-wallet" severity="warning" outlined @click="isAccountModalOpen = true" />
                    <Button label="Catat Transaksi" icon="pi pi-plus" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" @click="isTxModalOpen = true" />
                </div>
            </div>

            <!-- Financial Summary widgets -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Uang Masuk</span></template>
                    <template #content><span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(totalIncome) }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Uang Keluar</span></template>
                    <template #content><span class="text-2xl font-black text-red-500">{{ formatCurrency(totalExpense) }}</span></template>
                </Card>
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-xs font-bold uppercase tracking-wider text-gray-400">Arus Kas Bersih</span></template>
                    <template #content>
                        <span :class="['text-2xl font-black', netCashFlow >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-500']">
                            {{ formatCurrency(netCashFlow) }}
                        </span>
                    </template>
                </Card>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Rekening Kas</label>
                    <Dropdown v-model="filterAccount" :options="accountOptions" optionLabel="name" optionValue="id" class="w-full" @change="applyFilters" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Tanggal Mulai</label>
                    <input type="date" v-model="filterStartDate" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" @change="applyFilters" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Tanggal Akhir</label>
                    <input type="date" v-model="filterEndDate" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" @change="applyFilters" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Tipe</label>
                    <Dropdown v-model="filterType" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                </div>
            </div>

            <!-- Kas Accounts listing -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div v-for="acc in accounts" :key="acc.id" class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 flex justify-between items-center shadow-sm">
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ acc.name }}</h4>
                        <span class="text-[10px] uppercase text-gray-400 font-semibold">{{ acc.type }}</span>
                    </div>
                    <span class="font-bold text-sm text-gray-900 dark:text-white">{{ formatCurrency(acc.balance) }}</span>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Tanggal</th>
                                <th scope="col" class="px-6 py-4 font-bold">Keterangan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Sumber Kas</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Debit (+)</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Kredit (-)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="ledger in ledgers.data" :key="ledger.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4">{{ formatDate(ledger.date) }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                                    {{ ledger.description }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ ledger.account?.name }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-650">
                                    {{ ledger.type === 'in' ? formatCurrency(ledger.amount) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-red-500">
                                    {{ ledger.type === 'out' ? formatCurrency(ledger.amount) : '-' }}
                                </td>
                            </tr>
                            <tr v-if="ledgers.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Tidak ada log transaksi kas.</td>
                            </tr>
                        </tbody>
                    </table>
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

            <!-- Add Kas Account Modal -->
            <Dialog v-model:visible="isAccountModalOpen" modal header="Buat Rekening Kas Baru" class="w-full max-w-sm">
                <form @submit.prevent="submitAccount" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Rekening Kas <span class="text-red-500">*</span></label>
                        <InputText v-model="accountForm.name" required placeholder="Contoh: Bank BCA, Kas Kecil..." />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tipe Akun <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="accountForm.type"
                            :options="[{label: 'Tunai / Cash', value: 'cash'}, {label: 'Bank Transfer', value: 'bank'}, {label: 'E-Wallet', value: 'e-wallet'}]"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                            required
                        />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Saldo Awal (Rp)</label>
                        <InputNumber v-model="accountForm.balance" :min="0" placeholder="0" class="w-full" />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isAccountModalOpen = false" />
                        <Button type="submit" label="Simpan Rekening" :loading="accountForm.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                    </div>
                </form>
            </Dialog>

            <!-- Record Transaction Modal -->
            <Dialog v-model:visible="isTxModalOpen" modal header="Catat Transaksi Kas Baru" class="w-full max-w-md">
                <form @submit.prevent="submitTransaction" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Pilih Akun Kas <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="txForm.account_id"
                            :options="accounts"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Pilih rekening kas..."
                            class="w-full"
                            required
                        />
                    </div>
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
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Jumlah (Rp) <span class="text-red-500">*</span></label>
                        <InputNumber v-model="txForm.amount" :min="1" required placeholder="0" class="w-full" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Keterangan Transaksi <span class="text-red-500">*</span></label>
                        <InputText v-model="txForm.description" required placeholder="Misal: Biaya konsumsi meeting, Listrik..." />
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
