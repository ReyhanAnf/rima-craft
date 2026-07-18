<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const props = defineProps({
    customers: Array,
    products: Array,
});

const page = usePage();

const customerType = ref('registered'); // 'registered' or 'manual'

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    payment_status: 'paid',
    shipping_status: 'pending',
    customer_id: null,
    customer_name: '',
    customer_phone: '',
    save_customer: false,
    shipping_fee: 0,
    discount: 0,
    items: [
        { product_id: null, qty: 1, price: 0 }
    ],
});

const paymentOptions = [
    { label: 'Lunas (Paid)', value: 'paid' },
    { label: 'Belum Lunas (Unpaid)', value: 'unpaid' },
    { label: 'Sebagian (Partial)', value: 'partial' },
];

const shippingOptions = [
    { label: 'Menunggu (Pending)', value: 'pending' },
    { label: 'Dikirim (Shipped)', value: 'shipped' },
    { label: 'Diterima (Delivered)', value: 'delivered' },
];

const onProductSelect = (index) => {
    const item = form.items[index];
    const selectedProd = props.products.find(p => p.id === item.product_id);
    if (selectedProd) {
        item.price = Number(selectedProd.base_price) || 0;
    }
};

const addItem = () => {
    form.items.push({ product_id: null, qty: 1, price: 0 });
};

const removeItem = (idx) => {
    if (form.items.length > 1) {
        form.items.splice(idx, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.qty || 0) * Number(item.price || 0)), 0);
});

const grandTotal = computed(() => {
    const total = subtotal.value + Number(form.shipping_fee || 0) - Number(form.discount || 0);
    return total > 0 ? total : 0;
});

const submitForm = () => {
    form.clearErrors();
    form.post(route('sales.store'));
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
        <Head title="Catat Penjualan Baru" />

        <div class="max-w-4xl mx-auto pb-24 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('sales.index')">
                    <Button icon="pi pi-arrow-left" severity="secondary" rounded outlined />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Catat Penjualan Baru</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Stok produk jadi akan otomatis berkurang setelah faktur disimpan.</p>
                </div>
            </div>

            <!-- Validation Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Order & Shipping Info -->
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Informasi Faktur & Pengiriman</span></template>
                    <template #content>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Tanggal Penjualan <span class="text-red-500">*</span></label>
                                <input type="date" v-model="form.date" required class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white" />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Status Pembayaran <span class="text-red-500">*</span></label>
                                <Dropdown v-model="form.payment_status" :options="paymentOptions" optionLabel="label" optionValue="value" class="w-full" required />
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Status Pengiriman <span class="text-red-500">*</span></label>
                                <Dropdown v-model="form.shipping_status" :options="shippingOptions" optionLabel="label" optionValue="value" class="w-full" required />
                            </div>
                        </div>

                        <!-- Customer Selection -->
                        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 space-y-4">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="type_reg" value="registered" v-model="customerType" class="text-amber-500 focus:ring-amber-500" />
                                    <label for="type_reg" class="text-xs font-medium cursor-pointer">Pilih dari Buku Kontak</label>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="radio" id="type_manual" value="manual" v-model="customerType" class="text-amber-500 focus:ring-amber-500" />
                                    <label for="type_manual" class="text-xs font-medium cursor-pointer">Input Manual</label>
                                </div>
                            </div>

                            <!-- Registered Dropdown -->
                            <div v-if="customerType === 'registered'">
                                <Dropdown
                                    v-model="form.customer_id"
                                    :options="customers"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Pilih Customer..."
                                    filter
                                    class="w-full"
                                    :required="customerType === 'registered'"
                                />
                            </div>

                            <!-- Manual Inputs -->
                            <div v-if="customerType === 'manual'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Pelanggan <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.customer_name" placeholder="Nama Pelanggan..." :required="customerType === 'manual'" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">No. Telepon (Opsional)</label>
                                    <InputText v-model="form.customer_phone" placeholder="Contoh: 0812..." />
                                </div>
                                <div class="md:col-span-2 flex items-center gap-2">
                                    <Checkbox id="save_cus" v-model="form.save_customer" binary />
                                    <label for="save_cus" class="text-xs text-gray-500 cursor-pointer">Simpan info ini sebagai kontak baru di Buku Kontak</label>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Line Items Card -->
                <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                    <template #title>
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-850 pb-2">
                            <span class="text-sm font-bold uppercase tracking-wider text-gray-400">Produk yang Dijual</span>
                            <Button label="Tambah Baris" icon="pi pi-plus" size="small" outlined severity="warning" @click="addItem" />
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4 pt-2">
                            <div
                                v-for="(item, idx) in form.items"
                                :key="idx"
                                class="flex flex-col md:flex-row gap-4 items-start md:items-center bg-gray-50/50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200 dark:border-gray-800"
                            >
                                <!-- Product selection -->
                                <div class="w-full md:flex-1 flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Produk</label>
                                    <Dropdown
                                        v-model="item.product_id"
                                        :options="products"
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Pilih Produk..."
                                        filter
                                        class="w-full"
                                        required
                                        @change="onProductSelect(idx)"
                                    >
                                        <template #option="slotProps">
                                            <div class="flex justify-between items-center w-full">
                                                <span>{{ slotProps.option.name }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold">Stok: {{ slotProps.option.current_stock }}</span>
                                            </div>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Qty -->
                                <div class="w-full md:w-28 flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Qty</label>
                                    <InputNumber v-model="item.qty" :min="1" required class="w-full" showButtons buttonLayout="horizontal" inputClass="text-center w-12" />
                                </div>

                                <!-- Price -->
                                <div class="w-full md:w-44 flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Harga Satuan</label>
                                    <InputNumber v-model="item.price" :min="0" required class="w-full" mode="currency" currency="IDR" locale="id-ID" />
                                </div>

                                <!-- Line Subtotal & Delete -->
                                <div class="w-full md:w-48 flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider hidden md:block">Subtotal</label>
                                    <div class="flex justify-between items-center w-full h-10 md:h-11 border-t md:border-t-0 border-gray-150 dark:border-gray-800 pt-2 md:pt-0">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ formatCurrency(item.qty * item.price) }}
                                        </span>
                                        <Button
                                            icon="pi pi-trash"
                                            severity="danger"
                                            text
                                            rounded
                                            @click="removeItem(idx)"
                                            :disabled="form.items.length === 1"
                                            class="hover:bg-red-50 dark:hover:bg-red-950/20"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations Panel -->
                            <div class="border-t border-gray-150 dark:border-gray-800 pt-4 mt-6 space-y-3 text-right">
                                <div class="flex justify-between items-center md:justify-end md:gap-12">
                                    <span class="text-xs text-gray-500 font-semibold">Subtotal</span>
                                    <span class="font-bold text-sm text-gray-900 dark:text-white w-44">{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between items-center md:justify-end md:gap-12">
                                    <span class="text-xs text-gray-500 font-semibold">Ongkos Kirim (+)</span>
                                    <div class="w-44">
                                        <InputNumber v-model="form.shipping_fee" :min="0" mode="currency" currency="IDR" locale="id-ID" class="w-full" inputClass="text-right w-full font-semibold" />
                                    </div>
                                </div>
                                <div class="flex justify-between items-center md:justify-end md:gap-12">
                                    <span class="text-xs text-gray-500 font-semibold">Diskon (-)</span>
                                    <div class="w-44">
                                        <InputNumber v-model="form.discount" :min="0" mode="currency" currency="IDR" locale="id-ID" class="w-full" inputClass="text-right w-full font-semibold" />
                                    </div>
                                </div>
                                <div class="flex justify-between items-center md:justify-end md:gap-12 pt-3 border-t border-gray-150 dark:border-gray-800">
                                    <span class="text-sm font-bold uppercase text-gray-900 dark:text-white">Grand Total</span>
                                    <span class="text-xl font-bold text-amber-500 w-44">{{ formatCurrency(grandTotal) }}</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </Card>

                <!-- Action Footer CTA -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('sales.index')">
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
