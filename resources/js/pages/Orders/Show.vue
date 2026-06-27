<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';

const props = defineProps({
    order: Object,
});

const form = useForm({
    status: props.order.status,
    payment_status: props.order.payment_status,
    cancellation_reason: props.order.cancellation_reason || '',
    payment_proof: null,
});

const statusOptions = [
    { label: 'Pending', value: 'pending' },
    { label: 'Confirmed', value: 'confirmed' },
    { label: 'Processing', value: 'processing' },
    { label: 'Shipped', value: 'shipped' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' },
];

const paymentStatusOptions = [
    { label: 'Unpaid', value: 'unpaid' },
    { label: 'Paid', value: 'paid' },
    { label: 'Refunded', value: 'refunded' },
];

const submitStatus = () => {
    form.post(route('orders.update-status', props.order.id), {
        preserveState: true,
    });
};

const handleFileChange = (e) => {
    form.payment_proof = e.target.files[0];
};

const deleteOrder = () => {
    if (confirm(`Hapus pesanan ${props.order.order_number}?`)) {
        router.delete(route('orders.destroy', props.order.id));
    }
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
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// WhatsApp Link
const whatsappLink = computed(() => {
    let phone = (props.order.customer_phone || '').replace(/\D/g, '');
    if (phone.startsWith('0')) {
        phone = '62' + phone.substring(1);
    }
    const text = `Halo ${props.order.customer_name}, kami dari Rima Craft ingin mengonfirmasi pesanan Anda dengan nomor ${props.order.order_number}.`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
});
</script>

<template>
    <AdminLayout>
        <Head :title="`Detail Pesanan ${order.order_number}`" />

        <div class="space-y-6 max-w-4xl mx-auto pb-24">
            <!-- Header buttons -->
            <div class="flex justify-between items-center">
                <Link :href="route('orders.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Order Details & Customer Info -->
                <div class="md:col-span-2 space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-855 pb-3">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase tracking-widest">Detail Pesanan</div>
                                    <div class="text-lg font-bold text-gray-900 dark:text-white mt-0.5">{{ order.order_number }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-400">Metode</div>
                                    <div class="font-bold text-sm mt-0.5 uppercase">{{ order.payment_method }} ({{ order.order_method }})</div>
                                </div>
                            </div>
                        </template>
                        <template #content>
                            <!-- Customer Info -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs py-2">
                                <div>
                                    <span class="text-gray-400 font-bold block">Nama Lengkap</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm block mt-1">{{ order.customer_name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-bold block">No. Telepon / WA</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm block mt-1">{{ order.customer_phone }}</span>
                                </div>
                                <div class="sm:col-span-2">
                                    <span class="text-gray-400 font-bold block">Email</span>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm block mt-1">{{ order.customer_email || '-' }}</span>
                                </div>
                                <div class="sm:col-span-2 border-t border-gray-100 dark:border-gray-800 pt-3">
                                    <span class="text-gray-400 font-bold block">Alamat Pengiriman</span>
                                    <p class="font-medium text-gray-800 dark:text-gray-200 mt-1 leading-relaxed">{{ order.customer_address || '-' }}</p>
                                </div>
                                <div v-if="order.notes" class="sm:col-span-2 border-t border-gray-100 dark:border-gray-800 pt-3">
                                    <span class="text-gray-400 font-bold block">Catatan Pelanggan</span>
                                    <p class="text-gray-600 dark:text-gray-400 mt-1 italic">"{{ order.notes }}"</p>
                                </div>
                            </div>

                            <!-- WhatsApp CTA -->
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex">
                                <a :href="whatsappLink" target="_blank" class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-lg font-bold text-xs shadow-sm transition">
                                    <i class="pi pi-whatsapp"></i>
                                    Hubungi via WhatsApp
                                </a>
                            </div>
                        </template>
                    </Card>

                    <!-- Items Detail -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Rincian Belanja</span></template>
                        <template #content>
                            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                <div v-for="item in order.items" :key="item.id" class="flex justify-between items-center text-xs py-3 first:pt-0 last:pb-0">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-white">{{ item.name }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ item.qty }} pcs x {{ formatCurrency(item.price) }}</div>
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ formatCurrency(item.qty * item.price) }}
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-150 dark:border-gray-800 pt-4 mt-4 space-y-1.5 text-xs text-gray-500 text-right">
                                <div class="flex justify-end gap-12">
                                    <span>Subtotal:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(order.subtotal) }}</span>
                                </div>
                                <div v-if="order.shipping_cost > 0" class="flex justify-end gap-12">
                                    <span>Ongkir (+):</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(order.shipping_cost) }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-150 dark:border-gray-800 font-bold text-sm">
                                    <span class="text-gray-950 dark:text-white text-left">Grand Total</span>
                                    <span class="text-amber-600 dark:text-amber-400 text-base">{{ formatCurrency(order.total) }}</span>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Status & Action Forms -->
                <div class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Status & Perubahan</span></template>
                        <template #content>
                            <form @submit.prevent="submitStatus" class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pesanan</label>
                                    <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pembayaran</label>
                                    <Dropdown v-model="form.payment_status" :options="paymentStatusOptions" optionLabel="label" optionValue="value" class="w-full" />
                                </div>

                                <div v-if="form.status === 'cancelled'" class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold text-red-500">Alasan Pembatalan</label>
                                    <Textarea v-model="form.cancellation_reason" rows="2" placeholder="Sebutkan alasan..." class="w-full" />
                                </div>

                                <!-- Upload new Proof option -->
                                <div class="flex flex-col gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Ganti / Upload Bukti Transfer</label>
                                    <input type="file" accept="image/*" @change="handleFileChange" class="text-xs cursor-pointer w-full text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-amber-50 file:text-amber-700" />
                                </div>

                                <Button type="submit" label="Simpan Perubahan" class="w-full !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" :loading="form.processing" />
                            </form>
                        </template>
                    </Card>

                    <!-- Payment Proof Display Card -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Bukti Dokumen</span></template>
                        <template #content>
                            <div class="pt-2">
                                <div v-if="order.payment_proof" class="relative group w-full aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800">
                                    <img :src="`/storage/${order.payment_proof}`" class="w-full h-full object-cover" alt="" />
                                    <a :href="`/storage/${order.payment_proof}`" target="_blank" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition">
                                        Lihat Fullscreen
                                    </a>
                                </div>
                                <span v-else class="text-xs text-gray-400 italic">Belum ada dokumen bukti transfer diunggah.</span>
                            </div>
                        </template>
                    </Card>

                    <!-- Danger soft delete zone -->
                    <Card class="!border !border-red-200/50 dark:!border-red-950/50 !bg-red-50/20 dark:!bg-red-950/10">
                        <template #title><span class="text-xs font-bold uppercase tracking-wider text-red-500">Zona Bahaya</span></template>
                        <template #content>
                            <div class="pt-1 space-y-3">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Menghapus pesanan ini akan menyembunyikannya dari sistem (Soft Delete).</p>
                                <Button label="Hapus Pesanan" severity="danger" class="w-full" @click="deleteOrder" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
