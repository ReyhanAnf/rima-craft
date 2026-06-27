<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

const props = defineProps({
    purchase: Object,
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
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};

const totalPaid = computed(() => {
    return (props.purchase.payments || []).reduce((sum, p) => sum + Number(p.amount), 0);
});

const balanceDue = computed(() => {
    return props.purchase.grand_total - totalPaid.value;
});
</script>

<template>
    <AdminLayout>
        <Head :title="`Detail Pembelian ${purchase.invoice_number || purchase.id}`" />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Header buttons -->
            <div class="flex justify-between items-center">
                <Link :href="route('purchases.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Invoice Info -->
                <div class="md:col-span-2 space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-855 pb-3">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase tracking-widest">Detail Pembelian</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ purchase.invoice_number || `#${purchase.id}` }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Tanggal</div>
                                    <div class="font-semibold text-sm mt-0.5">{{ formatDate(purchase.date) }}</div>
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <!-- Supplier Details -->
                            <div class="py-2">
                                <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-2">Informasi Supplier</div>
                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ purchase.supplier?.name || purchase.supplier_name || 'Umum' }}
                                    <span v-if="!purchase.supplier" class="text-[9px] bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase">Manual</span>
                                </div>
                                <div v-if="purchase.supplier?.phone || purchase.supplier_phone" class="text-xs text-gray-500 mt-1">
                                    No. Telp: {{ purchase.supplier?.phone || purchase.supplier_phone }}
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-6">
                                <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider mb-3">Item Pembelian</h4>
                                <div class="space-y-3">
                                    <div v-for="item in purchase.items" :key="item.id" class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-3 last:border-0 last:pb-0">
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ item.material?.name || 'Bahan Dihapus' }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5">{{ item.qty }} {{ item.material?.unit }} x {{ formatCurrency(item.price) }}</div>
                                        </div>
                                        <div class="font-semibold text-gray-900 dark:text-white">
                                            {{ formatCurrency(item.qty * item.price) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Calculations -->
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-4 space-y-2 text-xs text-gray-500 text-right">
                                <div class="flex justify-between items-center pt-3 border-t border-gray-150 dark:border-gray-800 mt-3">
                                    <span class="font-bold text-sm text-gray-950 dark:text-white text-left">Grand Total</span>
                                    <span class="font-bold text-lg text-amber-600 dark:text-amber-400">{{ formatCurrency(purchase.grand_total) }}</span>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Status Management Panel -->
                <div class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Status Pembayaran</span></template>
                        <template #content>
                            <div class="pt-2">
                                <span :class="[
                                    'text-xs px-2.5 py-1 rounded-full font-bold uppercase',
                                    purchase.payment_status === 'paid'
                                        ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                        : purchase.payment_status === 'partial'
                                        ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                        : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
                                ]">
                                    {{ purchase.payment_status }}
                                </span>
                            </div>
                        </template>
                    </Card>

                    <!-- Cash Receipt Summary -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Ringkasan Dana</span></template>
                        <template #content>
                            <div class="space-y-3">
                                <!-- Paid Payments List -->
                                <div v-for="p in purchase.payments" :key="p.id" class="flex justify-between items-center text-xs border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-850 p-2.5 rounded-lg">
                                    <div>
                                        <div class="font-bold">{{ formatDate(p.date) }}</div>
                                        <div class="text-[10px] text-gray-400">Sumber: {{ p.account?.name }}</div>
                                    </div>
                                    <div class="font-bold text-red-650">{{ formatCurrency(p.amount) }}</div>
                                </div>
                                <div v-if="(purchase.payments || []).length === 0" class="text-center text-xs text-gray-400 py-2">Belum ada pembayaran</div>

                                <div class="border-t border-gray-150 dark:border-gray-800 pt-3 space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Total Dibayar:</span>
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
