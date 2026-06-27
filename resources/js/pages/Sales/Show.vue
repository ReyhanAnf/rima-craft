<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    sale: Object,
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

const shippingStatus = ref(props.sale.shipping_status);
const paymentStatus = ref(props.sale.payment_status);

const shippingOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'Dikirim (Shipped)', value: 'shipped' },
    { label: 'Diterima (Delivered)', value: 'delivered' },
];

const paymentOptions = [
    { label: 'Belum Lunas (Unpaid)', value: 'unpaid' },
    { label: 'Sebagian (Partial)', value: 'partial' },
    { label: 'Lunas (Paid)', value: 'paid' },
];

const updateShipping = () => {
    router.patch(route('sales.update-status', props.sale.id), {
        shipping_status: shippingStatus.value
    });
};

const updatePayment = () => {
    router.patch(route('sales.update-status', props.sale.id), {
        payment_status: paymentStatus.value
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
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const totalPaid = computed(() => {
    return (props.sale.payments || []).reduce((sum, p) => sum + Number(p.amount), 0);
});

const balanceDue = computed(() => {
    return props.sale.grand_total - totalPaid.value;
});
</script>

<template>
    <AdminLayout>
        <Head :title="`Faktur Penjualan ${sale.invoice_number || sale.id}`" />
        <Toast />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Header buttons -->
            <div class="flex justify-between items-center">
                <Link :href="route('sales.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                </Link>
                <div class="flex gap-2">
                    <a :href="route('sales.print', sale.id)" target="_blank">
                        <Button label="Cetak Invoice" icon="pi pi-print" severity="secondary" outlined />
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Invoice Info -->
                <div class="md:col-span-2 space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-855 pb-3">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase tracking-widest">Faktur Penjualan</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ sale.invoice_number || `#${sale.id}` }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Tanggal</div>
                                    <div class="font-semibold text-sm mt-0.5">{{ formatDate(sale.date) }}</div>
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <!-- Customer Details -->
                            <div class="py-2">
                                <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-2">Informasi Pelanggan</div>
                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ sale.customer?.name || sale.customer_name || 'Umum' }}
                                    <span v-if="!sale.customer" class="text-[9px] bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase">Non-Member</span>
                                </div>
                                <div v-if="sale.customer?.phone || sale.customer_phone" class="text-xs text-gray-500 mt-1">
                                    No. Telp: {{ sale.customer?.phone || sale.customer_phone }}
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-6">
                                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-3">Item Pembelian</h4>
                                <div class="space-y-3">
                                    <div v-for="item in sale.items" :key="item.id" class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-3 last:border-0 last:pb-0">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ item.product?.name || 'Produk Dihapus' }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">{{ item.qty }} pcs x {{ formatCurrency(item.price) }}</div>
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ formatCurrency(item.subtotal) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4 space-y-2 text-xs text-gray-500 text-right">
                                <div class="flex justify-end gap-6">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(sale.total_amount) }}</span>
                                </div>
                                <div v-if="sale.shipping_fee > 0" class="flex justify-end gap-6">
                                    <span>Ongkos Kirim (+):</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(sale.shipping_fee) }}</span>
                                </div>
                                <div v-if="sale.discount > 0" class="flex justify-end gap-6 text-emerald-600">
                                    <span>Diskon (-):</span>
                                    <span class="font-semibold">{{ formatCurrency(sale.discount) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-150 dark:border-gray-800 mt-3">
                                    <span class="font-bold text-sm text-gray-950 dark:text-white text-left">Grand Total</span>
                                    <span class="font-bold text-lg text-amber-600 dark:text-amber-400">{{ formatCurrency(sale.grand_total) }}</span>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Status Management Panel -->
                <div class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Pengaturan Status</span></template>
                        <template #content>
                            <div class="space-y-4">
                                <!-- Shipping Status Dropdown -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pengiriman</label>
                                    <Dropdown
                                        v-model="shippingStatus"
                                        :options="shippingOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        class="w-full"
                                        @change="updateShipping"
                                    />
                                </div>

                                <!-- Payment Status Dropdown -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pembayaran</label>
                                    <Dropdown
                                        v-model="paymentStatus"
                                        :options="paymentOptions"
                                        optionLabel="label"
                                        optionValue="value"
                                        class="w-full"
                                        @change="updatePayment"
                                    />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Cash Receipt Summary -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Ringkasan Dana</span></template>
                        <template #content>
                            <div class="space-y-3">
                                <!-- Paid Payments List -->
                                <div v-for="p in sale.payments" :key="p.id" class="flex justify-between items-center text-xs border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-850 p-2.5 rounded-lg">
                                    <div>
                                        <div class="font-bold">{{ formatDate(p.date) }}</div>
                                        <div class="text-[10px] text-gray-400">Kasir: Cash</div>
                                    </div>
                                    <div class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(p.amount) }}</div>
                                </div>
                                <div v-if="(sale.payments || []).length === 0" class="text-center text-xs text-gray-400 py-2">Belum ada pembayaran</div>

                                <div class="border-t border-gray-150 dark:border-gray-800 pt-3 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Total Diterima:</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ formatCurrency(totalPaid) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Sisa Tagihan:</span>
                                        <span class="font-bold text-red-500">{{ formatCurrency(balanceDue) }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
