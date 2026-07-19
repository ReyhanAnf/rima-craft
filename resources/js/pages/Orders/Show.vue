<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

const isProofModalOpen = ref(false);
const selectedProofPath = ref('');
const isUploadModalOpen = ref(false);
const uploadPreviewUrl = ref(null);

const openProofModal = (path) => {
    selectedProofPath.value = path;
    isProofModalOpen.value = true;
};

const props = defineProps({
    order: Object,
});

const uploadForm = useForm({
    payment_proof: null,
    payment_status: props.order.payment_status,
});

const handleUploadFileChange = (e) => {
    const file = e.target.files[0];
    uploadForm.payment_proof = file;
    if (file) {
        uploadPreviewUrl.value = URL.createObjectURL(file);
    } else {
        uploadPreviewUrl.value = null;
    }
};

const submitUploadProof = () => {
    if (!uploadForm.payment_proof) return;

    uploadForm.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('orders.update-status', props.order.id), {
        preserveState: true,
        onSuccess: () => {
            isUploadModalOpen.value = false;
            uploadPreviewUrl.value = null;
            uploadForm.reset('payment_proof');
        }
    });
};

const form = useForm({
    status: props.order.status,
    payment_status: props.order.payment_status,
    cancellation_reason: props.order.cancellation_reason || '',
    payment_proof: null,
    tracking_number: props.order.tracking_number || '',
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
    // Laravel cannot parse multipart form data with PATCH directly.
    // We spoof the method by posting with _method: 'PATCH'
    form.transform((data) => ({
        ...data,
        _method: 'PATCH',
    })).post(route('orders.update-status', props.order.id), {
        preserveState: true,
        onSuccess: () => {
            previewUrl.value = null;
        }
    });
};

const previewUrl = ref(null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    form.payment_proof = file;
    if (file) {
        previewUrl.value = URL.createObjectURL(file);
    } else {
        previewUrl.value = null;
    }
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

        <div class="space-y-6 w-full max-w-[1400px] mx-auto pb-24">
            <!-- Header buttons -->
            <div class="flex justify-between items-center">
                <Link :href="route('orders.index')">
                    <Button label="Kembali" icon="pi pi-arrow-left" severity="secondary" text />
                </Link>
                <div class="flex gap-2">
                    <a :href="route('orders.pdf', order.id)" download>
                        <Button label="Download PDF" icon="pi pi-file-pdf" severity="warning" />
                    </a>
                    <a :href="route('orders.print', order.id)">
                        <Button label="Lihat Invoice" icon="pi pi-print" severity="secondary" outlined />
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Order Details & Customer Info -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-6">
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
                                <a :href="whatsappLink" class="w-full flex items-center justify-center gap-2 py-2.5 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-lg font-bold text-xs shadow-sm transition">
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
                                        <div v-if="item.variantLabel" class="text-[10px] font-bold text-amber-600 dark:text-amber-400 mt-0.5">Varian: {{ item.variantLabel }}</div>
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

                    <!-- Quick Verification Actions (Highly visible for non-technical admins) -->
                    <!-- Interactive 6-Step Order Processing Workflow -->
                    <Card v-if="order.status !== 'cancelled'" class="!border-2 !border-amber-300 dark:!border-amber-800/80 !bg-white dark:!bg-gray-900 shadow-md">
                        <template #title>
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                <i class="pi pi-directions"></i>
                                Alur Pemrosesan Pesanan (6 Langkah)
                            </span>
                        </template>
                        <template #content>
                            <div class="relative pl-6 border-l-2 border-gray-200 dark:border-gray-800 space-y-8 py-2">
                                
                                <!-- STEP 1: Konfirmasi Pembayaran -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.payment_status === 'paid' || order.payment_status === 'partial' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 1: Konfirmasi Pembayaran</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[
                                                      order.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' :
                                                      order.payment_status === 'partial' ? 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400' :
                                                      'bg-red-50 text-red-650 dark:bg-red-500/10 dark:text-red-400'
                                                  ]">
                                                {{ order.payment_status === 'paid' ? 'LUNAS' : order.payment_status === 'partial' ? 'BELUM LUNAS (Bayar Belakangan / COD)' : 'BELUM DIBAYAR' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Lakukan verifikasi pembayaran. Anda dapat mengunggah bukti transfer untuk menandai sebagai Lunas, atau tandai sebagai Belum Lunas (untuk Cash on Delivery / COD, bayar ketika sampai, DP, atau tempo).
                                        </p>
                                        
                                        <!-- Actions if not paid or partial -->
                                        <div v-if="order.status !== 'completed' && order.payment_status === 'unpaid'" class="mt-2 space-y-3 bg-gray-50 dark:bg-gray-950 p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                                            <div class="flex flex-col gap-2">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Opsi A: Upload Bukti Transfer & Lunaskan</label>
                                                <div class="relative group border border-dashed border-gray-200 dark:border-gray-800 hover:border-amber-400 rounded-lg p-2 transition flex flex-col items-center justify-center cursor-pointer min-h-[70px] bg-white dark:bg-gray-900">
                                                    <input type="file" accept="image/*" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                                    <div v-if="!previewUrl" class="text-center">
                                                        <i class="pi pi-upload text-gray-400 text-sm block"></i>
                                                        <span class="text-[10px] font-semibold text-gray-500 block">Pilih Bukti Transfer</span>
                                                    </div>
                                                    <div v-else class="w-full flex items-center gap-3">
                                                        <img :src="previewUrl" class="w-10 h-10 object-cover rounded border" />
                                                        <span class="text-[10px] text-gray-500 truncate flex-1">{{ form.payment_proof?.name }}</span>
                                                    </div>
                                                </div>
                                                <Button 
                                                    label="Upload & Konfirmasi Lunas" 
                                                    icon="pi pi-wallet" 
                                                    severity="success" 
                                                    class="w-full text-[10px] font-bold py-1.5" 
                                                    @click="() => { form.payment_status = 'paid'; submitStatus(); }"
                                                    :loading="form.processing"
                                                />
                                            </div>
                                            
                                            <div class="border-t border-gray-200 dark:border-gray-800 pt-2 flex flex-col gap-2">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Opsi B: Bayar di Akhir / COD / DP / Tempo</label>
                                                <Button 
                                                    label="Tandai Sebagai Belum Lunas (Bayar Belakangan / COD)" 
                                                    icon="pi pi-clock" 
                                                    severity="warning" 
                                                    outlined
                                                    class="w-full text-[10px] font-bold py-1.5" 
                                                    @click="() => { form.payment_status = 'partial'; submitStatus(); }"
                                                    :loading="form.processing"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STEP 2: Konfirmasi Pesanan -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.status !== 'pending' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 2: Konfirmasi Pesanan</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[order.status !== 'pending' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-amber-50 text-amber-600 dark:bg-amber-500/10']">
                                                {{ order.status !== 'pending' ? 'DISETUJUI' : 'MENUNGGU' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Setelah pembayaran diverifikasi (baik Lunas maupun Partial/DP), setujui pesanan ini agar tim produksi dapat memproses barang.
                                        </p>
                                        
                                        <!-- Actions if pending -->
                                        <div v-if="order.status === 'pending'" class="mt-2 flex gap-2">
                                            <Button 
                                                label="Setujui & Konfirmasi Pesanan" 
                                                icon="pi pi-check-circle" 
                                                severity="success" 
                                                class="w-full text-[10px] font-bold py-2" 
                                                @click="() => { form.status = 'confirmed'; submitStatus(); }"
                                                :loading="form.processing"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STEP 3: Pengemasan -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.status === 'processing' || order.status === 'shipped' || order.status === 'completed' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 3: Pengemasan (Proses Gudang)</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[(order.status === 'processing' || order.status === 'shipped' || order.status === 'completed') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-gray-100 text-gray-500']">
                                                {{ (order.status === 'processing' || order.status === 'shipped' || order.status === 'completed') ? 'PROSES KEMAS / SELESAI' : 'BELUM DIMULAI' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Tandai pesanan sebagai sedang dikemas atau diproses ketika barang mulai disiapkan di gudang.
                                        </p>
                                        
                                        <!-- Actions if confirmed -->
                                        <div v-if="order.status === 'confirmed'" class="mt-2">
                                            <Button 
                                                label="Mulai Proses Pengemasan" 
                                                icon="pi pi-spin pi-spinner" 
                                                severity="info" 
                                                class="w-full text-[10px] font-bold py-2" 
                                                @click="() => { form.status = 'processing'; submitStatus(); }"
                                                :loading="form.processing"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STEP 4: Pengiriman (Memasukkan Resi) -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.status === 'shipped' || order.status === 'completed' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 4: Pengiriman & Input Resi</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[(order.status === 'shipped' || order.status === 'completed') ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-gray-100 text-gray-500']">
                                                {{ (order.status === 'shipped' || order.status === 'completed') ? 'DIKIRIM' : 'BELUM DIKIRIM' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Masukkan nomor resi ekspedisi pengiriman untuk mengubah status pesanan menjadi dikirim. **Nomor Resi wajib diisi.**
                                        </p>
                                        
                                        <div v-if="order.tracking_number" class="p-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-800 rounded-lg text-xs">
                                            <span class="text-gray-400 block font-bold text-[9px] uppercase tracking-wider">No. Resi Pengiriman:</span>
                                            <span class="font-mono font-bold text-gray-900 dark:text-white">{{ order.tracking_number }}</span>
                                        </div>
                                        
                                        <!-- Actions if processing -->
                                        <div v-if="order.status === 'processing'" class="mt-2 space-y-2 bg-gray-50 dark:bg-gray-950 p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                                            <div class="flex flex-col gap-1">
                                                <label class="text-[10px] font-bold text-gray-400 uppercase">Nomor Resi Pengiriman <span class="text-red-500">*</span></label>
                                                <InputText v-model="form.tracking_number" placeholder="Masukkan nomor resi..." class="w-full text-xs !py-1.5" required />
                                            </div>
                                            <Button 
                                                label="Kirim Barang & Simpan Resi" 
                                                icon="pi pi-send" 
                                                severity="info" 
                                                class="w-full text-[10px] font-bold py-2" 
                                                :disabled="!form.tracking_number"
                                                @click="() => { form.status = 'shipped'; submitStatus(); }"
                                                :loading="form.processing"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STEP 5: Konfirmasi Penerimaan -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.status === 'completed' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 5: Konfirmasi Penerimaan</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[order.status === 'completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-gray-100 text-gray-500']">
                                                {{ order.status === 'completed' ? 'DITERIMA' : 'DALAM PERJALANAN' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Konfirmasi bahwa barang telah sampai dan diterima secara utuh oleh pihak pembeli.
                                        </p>
                                        
                                        <!-- Actions if shipped -->
                                        <div v-if="order.status === 'shipped'" class="mt-2">
                                            <Button 
                                                label="Konfirmasi Barang Sudah Sampai / Diterima" 
                                                icon="pi pi-home" 
                                                severity="success" 
                                                class="w-full text-[10px] font-bold py-2" 
                                                @click="() => { form.status = 'completed'; submitStatus(); }"
                                                :loading="form.processing"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- STEP 6: Selesaikan Pesanan -->
                                <div class="relative">
                                    <!-- Step Dot -->
                                    <div class="absolute -left-[31px] top-0 w-4 h-4 rounded-full border-2 bg-white transition-all duration-300" 
                                         :class="[order.status === 'completed' && order.payment_status === 'paid' ? 'border-emerald-500 bg-emerald-500 shadow-sm shadow-emerald-500/20' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900']">
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center">
                                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Langkah 6: Selesaikan Pesanan & Pembayaran</h4>
                                            <span class="text-[10px] font-bold px-2 py-0.5 rounded uppercase"
                                                  :class="[order.status === 'completed' && order.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10' : 'bg-gray-100 text-gray-500']">
                                                {{ (order.status === 'completed' && order.payment_status === 'paid') ? 'SELESAI LUNAS' : 'PENDING' }}
                                            </span>
                                        </div>
                                        
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                            Tandai pesanan lunas secara penuh (jika sebelumnya partial/DP) dan selesaikan transaksi secara formal.
                                        </p>
                                        
                                        <!-- Actions if completed but not paid -->
                                        <div v-if="order.status === 'completed' && order.payment_status !== 'paid'" class="mt-2">
                                            <Button 
                                                label="Selesaikan Pembayaran (Lunas)" 
                                                icon="pi pi-check-circle" 
                                                severity="success" 
                                                class="w-full text-[10px] font-bold py-2" 
                                                @click="() => { form.payment_status = 'paid'; submitStatus(); }"
                                                :loading="form.processing"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Status & Action Forms -->
                <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Pengaturan Manual Status</span></template>
                        <template #content>
                            <div v-if="order.status === 'completed' || order.status === 'cancelled'" class="p-3 bg-gray-50 dark:bg-gray-950 border border-gray-150 dark:border-gray-850 rounded-lg text-xs text-gray-500 font-semibold mb-4 text-center">
                                <i class="pi pi-lock mr-1.5 text-[10px]"></i>
                                Pesanan ini telah {{ order.status === 'completed' ? 'Selesai' : 'Dibatalkan' }}. Data status terkunci.
                            </div>
                            <form @submit.prevent="submitStatus" class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pesanan</label>
                                    <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" :disabled="order.status === 'completed' || order.status === 'cancelled'" />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Status Pembayaran</label>
                                    <Dropdown v-model="form.payment_status" :options="paymentStatusOptions" optionLabel="label" optionValue="value" class="w-full" :disabled="order.status === 'completed' || order.status === 'cancelled'" />
                                </div>

                                <div v-if="form.status === 'cancelled'" class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold text-red-500">Alasan Pembatalan</label>
                                    <Textarea v-model="form.cancellation_reason" rows="2" placeholder="Sebutkan alasan..." class="w-full" :disabled="order.status === 'completed' || order.status === 'cancelled'" />
                                </div>

                                <!-- Upload new Proof option -->
                                <div v-if="order.status !== 'completed' && order.status !== 'cancelled'" class="flex flex-col gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-800">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase">Ganti / Upload Bukti Transfer</label>
                                    <input type="file" accept="image/*" @change="handleFileChange" class="text-xs cursor-pointer w-full text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-amber-50 file:text-amber-700" />
                                </div>

                                <Button v-if="order.status !== 'completed' && order.status !== 'cancelled'" type="submit" label="Simpan Perubahan" severity="warning" outlined class="w-full font-bold" :loading="form.processing" />
                            </form>
                        </template>
                    </Card>

                    <!-- Payment Proof Display Card (Stacked History) -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold uppercase tracking-wider text-gray-400">Riwayat Bukti Transfer</span>
                                <span v-if="order.payment_proofs_list && order.payment_proofs_list.length > 0" class="text-[10px] font-bold px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                                    {{ order.payment_proofs_list.length }} Dokumen
                                </span>
                            </div>
                        </template>
                        <template #content>
                            <div class="pt-2 space-y-4">
                                <div v-if="order.payment_proofs_list && order.payment_proofs_list.length > 0" class="grid grid-cols-2 gap-3">
                                    <div v-for="(proof, index) in order.payment_proofs_list" :key="index" class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <img :src="`/storage/${proof}`" @click="openProofModal(proof)" class="w-full h-full object-cover cursor-pointer" :alt="`Bukti ${index + 1}`" />
                                        <div class="absolute top-1.5 left-1.5 bg-black/70 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">
                                            #{{ index + 1 }}
                                        </div>
                                        <button type="button" @click="openProofModal(proof)" class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs font-bold transition cursor-pointer">
                                            <i class="pi pi-search-plus mr-1"></i> Zoom
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-gray-400 italic py-1">Belum ada dokumen bukti transfer diunggah.</div>

                                <!-- Button to trigger upload modal -->
                                <Button 
                                    label="Upload Bukti Pembayaran Lagi" 
                                    icon="pi pi-upload" 
                                    severity="warning" 
                                    outlined
                                    class="w-full text-xs font-bold" 
                                    @click="isUploadModalOpen = true" 
                                />
                            </div>
                        </template>
                    </Card>

                    <!-- Dialog Lightbox Bukti Pembayaran -->
                    <Dialog v-model:visible="isProofModalOpen" modal header="Bukti Pembayaran" :style="{ width: '90vw', maxWidth: '650px' }">
                        <div class="flex justify-center items-center p-2 bg-white dark:bg-gray-900 rounded-lg">
                            <img :src="`/storage/${selectedProofPath}`" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-md" alt="Bukti Transfer Full" />
                        </div>
                    </Dialog>

                    <!-- Dialog Modal Upload Bukti Pembayaran Lagi -->
                    <Dialog v-model:visible="isUploadModalOpen" modal header="Upload Bukti Pembayaran Lagi" :style="{ width: '90vw', maxWidth: '480px' }">
                        <form @submit.prevent="submitUploadProof" class="space-y-4 pt-2">
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Bukti transfer baru ini akan ditambahkan ke riwayat tanpa menghapus bukti transfer sebelumnya.
                            </p>
                            
                            <div class="flex flex-col gap-2">
                                <label class="text-xs font-bold text-gray-700 dark:text-gray-300">Pilih Foto / Struk Bukti Transfer <span class="text-red-500">*</span></label>
                                <div class="relative group border-2 border-dashed border-gray-200 dark:border-gray-800 hover:border-amber-400 rounded-lg p-4 transition flex flex-col items-center justify-center cursor-pointer min-h-[110px] bg-gray-50 dark:bg-gray-950">
                                    <input type="file" accept="image/*" @change="handleUploadFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required />
                                    <div v-if="!uploadPreviewUrl" class="text-center">
                                        <i class="pi pi-cloud-upload text-amber-500 text-2xl block mb-1"></i>
                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 block">Klik di sini untuk memilih file</span>
                                    </div>
                                    <div v-else class="w-full flex items-center gap-3">
                                        <img :src="uploadPreviewUrl" class="w-14 h-14 object-cover rounded border" />
                                        <span class="text-xs text-gray-700 dark:text-gray-200 font-medium truncate flex-1">{{ uploadForm.payment_proof?.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="text-xs font-semibold">Ubah Status Pembayaran (Opsional)</label>
                                <Dropdown v-model="uploadForm.payment_status" :options="paymentStatusOptions" optionLabel="label" optionValue="value" class="w-full text-xs" />
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <Button label="Batal" severity="secondary" text text-xs @click="isUploadModalOpen = false" />
                                <Button type="submit" label="Unggah Bukti Baru" icon="pi pi-check" severity="warning" class="font-bold text-xs" :loading="uploadForm.processing" :disabled="!uploadForm.payment_proof" />
                            </div>
                        </form>
                    </Dialog>

                    <!-- Danger soft delete zone -->
                    <Card class="!border !border-red-200/50 dark:!border-red-950/50 !bg-red-50/20 dark:!bg-red-950/10">
                        <template #title><span class="text-xs font-bold uppercase tracking-wider text-red-500">Zona Bahaya</span></template>
                        <template #content>
                            <div class="pt-1 space-y-3">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Menghapus pesanan ini akan menyembunyikannya dari sistem (Soft Delete).</p>
                                <Button label="Hapus Pesanan" severity="danger" outlined class="w-full" @click="deleteOrder" />
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
