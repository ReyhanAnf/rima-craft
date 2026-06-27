<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    suppliers: Array,
    materials: Array,
});

const toast = useToast();
const supplierType = ref('registered'); // 'registered' or 'manual'

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    payment_status: 'paid',
    supplier_id: null,
    supplier_name: '',
    supplier_phone: '',
    save_supplier: false,
    items: [
        { material_id: null, qty: 1, price: 0 }
    ],
});

const paymentOptions = [
    { label: 'Lunas (Paid)', value: 'paid' },
    { label: 'Belum Lunas (Unpaid)', value: 'unpaid' },
    { label: 'Sebagian (Partial)', value: 'partial' },
];

const onMaterialSelect = (index) => {
    const item = form.items[index];
    const selectedMat = props.materials.find(m => m.id === item.material_id);
    if (selectedMat) {
        item.price = Number(selectedMat.last_buy_price) || 0;
    }
};

const addItem = () => {
    form.items.push({ material_id: null, qty: 1, price: 0 });
};

const removeItem = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.price || 0)), 0);
});

const getMaterialUnit = (materialId) => {
    const mat = props.materials.find(m => m.id === materialId);
    return mat ? mat.unit : '';
};

const submitForm = () => {
    form.clearErrors();
    form.post(route('purchases.store'));
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <AdminLayout>
        <Head title="Catat Pembelian Baru" />
        <Toast />

        <div class="max-w-4xl mx-auto pb-24 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('purchases.index')">
                    <Button icon="pi pi-arrow-left" severity="secondary" rounded outlined />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Catat Pembelian Baru</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Stok bahan baku akan bertambah otomatis setelah transaksi disimpan.</p>
                </div>
            </div>

            <!-- Validation Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Transaction info -->
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Informasi Transaksi</span></template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Tanggal Pembelian <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.date" required class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Status Pembayaran <span class="text-red-500">*</span></label>
                                <Dropdown v-model="form.payment_status" :options="paymentOptions" optionLabel="label" optionValue="value" class="w-full" required />
                            </div>
                        </div>

                        <!-- Supplier Selection -->
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="sup_reg" value="registered" v-model="supplierType" class="text-amber-500 focus:ring-amber-500" />
                                    <label for="sup_reg" class="text-xs font-medium cursor-pointer">Pilih dari Buku Kontak</label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="sup_manual" value="manual" v-model="supplierType" class="text-amber-500 focus:ring-amber-500" />
                                    <label for="sup_manual" class="text-xs font-medium cursor-pointer">Input Manual</label>
                                </div>
                            </div>

                            <!-- Registered Dropdown -->
                            <div v-if="supplierType === 'registered'">
                                <Dropdown
                                    v-model="form.supplier_id"
                                    :options="suppliers"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Pilih Supplier..."
                                    filter
                                    class="w-full"
                                    :required="supplierType === 'registered'"
                                />
                            </div>

                            <!-- Manual Inputs -->
                            <div v-if="supplierType === 'manual'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Supplier <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.supplier_name" placeholder="Nama Supplier..." :required="supplierType === 'manual'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">No. Telepon (Opsional)</label>
                                    <InputText v-model="form.supplier_phone" placeholder="Contoh: 0812..." />
                                </div>
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <Checkbox id="save_sup" v-model="form.save_supplier" binary />
                                    <label for="save_sup" class="text-xs text-gray-500 cursor-pointer">Simpan info ini sebagai kontak baru di Buku Kontak</label>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Line Items Card -->
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title>
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-850 pb-2">
                            <span class="text-sm font-bold uppercase tracking-wider text-gray-400">Bahan Baku yang Dibeli</span>
                            <Button label="Tambah Baris" icon="pi pi-plus" size="small" outlined severity="warning" @click="addItem" />
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4 pt-2">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="flex flex-col md:flex-row gap-3 items-end bg-gray-50/50 dark:bg-gray-800/40 p-3 rounded-lg border border-gray-200 dark:border-gray-800"
                            >
                                <!-- Material Selection -->
                                <div class="w-full md:flex-1 flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Pilih Bahan</label>
                                    <Dropdown
                                        v-model="item.material_id"
                                        :options="materials"
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Pilih Bahan..."
                                        filter
                                        class="w-full"
                                        required
                                        @change="onMaterialSelect(idx)"
                                    />
                                </div>

                                <!-- Qty -->
                                <div class="w-full md:w-28 flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Qty ({{ getMaterialUnit(item.material_id) || 'satuan' }})</label>
                                    <InputNumber v-model="item.qty" :min="0.01" :maxFractionDigits="2" required class="w-full" />
                                </div>

                                <!-- Price -->
                                <div class="w-full md:w-36 flex flex-col gap-1">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Harga Satuan</label>
                                    <InputNumber v-model="item.price" :min="0" required class="w-full" />
                                </div>

                                <!-- Line Subtotal & Delete -->
                                <div class="w-full md:w-40 flex justify-between items-center pt-2 md:pt-0">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(item.qty * item.price) }}
                                    </div>
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        @click="removeItem(idx)"
                                        :disabled="form.items.length === 1"
                                    />
                                </div>
                            </div>

                            <!-- Calculations Panel -->
                            <div class="border-t border-gray-150 dark:border-gray-800 pt-4 mt-6 space-y-3 text-right">
                                <div class="flex justify-between items-center md:justify-end md:gap-12">
                                    <span class="text-sm font-bold uppercase text-gray-900 dark:text-white">Grand Total</span>
                                    <span class="text-xl font-bold text-amber-500 w-32">{{ formatCurrency(subtotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Action Footer CTA -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('purchases.index')">
                        <Button label="Batal" severity="secondary" text />
                    </Link>
                    <Button
                        type="submit"
                        label="Simpan Transaksi"
                        icon="pi pi-save"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
