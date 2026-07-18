<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

const props = defineProps({
    adjustments: Object,
    materials: Array,
    products: Array,
    filters: Object,
});

const page = usePage();

// Filters
const filterType = ref(props.filters.type || '');
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

const typeOptions = [
    { label: 'Semua Aksi', value: '' },
    { label: 'Penambahan (+)', value: 'in' },
    { label: 'Pengurangan (-)', value: 'out' },
];

const applyFilters = () => {
    router.get(route('stock-adjustments.index'), {
        type: filterType.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
};

// Form State
const isFormOpen = ref(false);
const itemType = ref('Product'); // 'Product' or 'Material'

const form = useForm({
    adjustable_type: 'product',
    adjustable_id: null,
    actual_stock: 0,
    type: 'in',
    quantity: 1,
    reason: '',
});

watch(itemType, (newType) => {
    form.adjustable_type = newType === 'Product' ? 'product' : 'material';
    form.adjustable_id = null;
    form.actual_stock = 0;
});

// Watch current stock and updates actual_stock automatically
const currentStockOfSelectedItem = computed(() => {
    const list = itemType.value === 'Product' ? props.products : props.materials;
    const item = list.find(i => i.id === form.adjustable_id);
    return item ? Number(item.current_stock) : 0;
});

const submitForm = () => {
    // hitung actual_stock
    const current = currentStockOfSelectedItem.value;
    if (form.type === 'in') {
        form.actual_stock = current + Number(form.quantity);
    } else {
        form.actual_stock = Math.max(0, current - Number(form.quantity));
    }

    form.post(route('stock-adjustments.store'), {
        onSuccess: () => {
            isFormOpen.value = false;
            form.reset();
        }
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AdminLayout>
        <Head title="Penyesuaian Stok" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Penyesuaian Stok</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola log penyesuaian stok produk jadi dan bahan baku.</p>
                </div>
                <Button
                    label="Sesuaikan Stok"
                    icon="pi pi-plus"
                    class="w-full sm:w-auto justify-center !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold self-stretch sm:self-start md:self-auto"
                    @click="isFormOpen = true"
                />
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Aksi Penyesuaian</label>
                    <Dropdown v-model="filterType" :options="typeOptions" optionLabel="label" optionValue="value" class="w-full" @change="applyFilters" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-505 uppercase tracking-wider">Rentang Tanggal Log</label>
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

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Waktu</th>
                                <th scope="col" class="px-6 py-4 font-bold">Item</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tipe Item</th>
                                <th scope="col" class="px-6 py-4 font-bold">Penyesuaian</th>
                                <th scope="col" class="px-6 py-4 font-bold">Alasan</th>
                                <th scope="col" class="px-6 py-4 font-bold">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="adj in adjustments.data" :key="adj.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400">{{ formatDate(adj.created_at) }}</td>
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ adj.adjustable?.name || 'Item Dihapus' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-0.5 rounded font-semibold text-[10px] uppercase tracking-wider', adj.adjustable_type.includes('Product') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400']">
                                        {{ adj.adjustable_type.includes('Product') ? 'Produk Jadi' : 'Bahan Baku' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold">
                                    <span :class="['text-xs px-2 py-0.5 rounded font-bold uppercase inline-flex items-center gap-1', adj.type === 'in' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-650' : 'bg-red-50 dark:bg-red-500/10 text-red-600']">
                                        {{ adj.type === 'in' ? '+' : '-' }}{{ adj.quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs max-w-xs truncate">{{ adj.reason || '-' }}</td>
                                <td class="px-6 py-4 text-xs">{{ adj.user?.name || '-' }}</td>
                            </tr>
                            <tr v-if="adjustments.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada log penyesuaian stok.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div
                        v-for="adj in adjustments.data"
                        :key="adj.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">
                                    {{ adj.adjustable?.name || 'Item Dihapus' }}
                                </h4>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ formatDate(adj.created_at) }}</p>
                            </div>
                            <span :class="['shrink-0 px-2 py-0.5 rounded font-semibold text-[10px] uppercase tracking-wider', adj.adjustable_type.includes('Product') ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400']">
                                {{ adj.adjustable_type.includes('Product') ? 'Produk Jadi' : 'Bahan Baku' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span :class="['text-xs px-2 py-1 rounded font-bold uppercase inline-flex items-center gap-1', adj.type === 'in' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-650' : 'bg-red-50 dark:bg-red-500/10 text-red-600']">
                                {{ adj.type === 'in' ? '+' : '-' }}{{ adj.quantity }}
                            </span>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                Oleh: {{ adj.user?.name || '-' }}
                            </span>
                        </div>

                        <div>
                            <p class="text-[10px] font-bold uppercase text-gray-400 tracking-wide">Alasan</p>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                                {{ adj.reason || '-' }}
                            </p>
                        </div>
                    </div>
                    <div v-if="adjustments.data.length === 0" class="p-6 text-center text-gray-400">Tidak ada log penyesuaian stok.</div>
                </div>
            </div>

            <!-- Form Dialog Modal -->
            <Dialog
                v-model:visible="isFormOpen"
                modal
                header="Catat Penyesuaian Stok"
                class="w-full max-w-md !rounded-2xl"
                :breakpoints="{ '960px': '75vw', '641px': '90vw' }"
            >
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <!-- Type selector -->
                    <div class="flex flex-col gap-2">
                        <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tipe Komoditas <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2 bg-gray-100/80 dark:bg-gray-800/80 p-1 rounded-lg">
                            <button
                                type="button"
                                @click="itemType = 'Product'"
                                :class="['py-2 text-xs font-bold rounded-md transition-all duration-200', itemType === 'Product' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white']"
                            >
                                Produk Jadi
                            </button>
                            <button
                                type="button"
                                @click="itemType = 'Material'"
                                :class="['py-2 text-xs font-bold rounded-md transition-all duration-200', itemType === 'Material' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 hover:text-gray-950 dark:text-gray-400 dark:hover:text-white']"
                            >
                                Bahan Baku
                            </button>
                        </div>
                    </div>

                    <!-- Target Item Dropdown -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pilih Item <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.adjustable_id"
                            :options="itemType === 'Product' ? products : materials"
                            optionLabel="name"
                            optionValue="id"
                            placeholder="Pilih item..."
                            filter
                            class="w-full"
                            required
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Adjustment Type -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Aksi <span class="text-red-500">*</span></label>
                            <Dropdown
                                v-model="form.type"
                                :options="[{ label: 'Penambahan (+)', value: 'in' }, { label: 'Pengurangan (-)', value: 'out' }]"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                                required
                            />
                        </div>
                        <!-- Quantity -->
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Jumlah <span class="text-red-500">*</span></label>
                            <InputNumber v-model="form.quantity" :min="1" required class="w-full" inputClass="w-full" />
                        </div>
                    </div>

                    <!-- Reason -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Alasan / Catatan <span class="text-red-500">*</span></label>
                        <Textarea v-model="form.reason" rows="3" required placeholder="Tuliskan detail penyesuaian..." class="w-full !rounded-lg border border-gray-200 dark:border-gray-800 p-2.5 text-sm bg-white dark:bg-gray-950 text-gray-900 dark:text-white" />
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-850">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            label="Simpan Penyesuaian"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
