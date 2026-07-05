<script setup>
import { ref, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useCartStore } from '@/stores/cart';

const props = defineProps({
    order: { type: Object, required: true },
});

const page = usePage();
const cart = useCartStore();
const config = page.props.siteConfig ?? {};

cart.clear();

const copied = ref(false);

function copyAccountNumber(number) {
    navigator.clipboard.writeText(number).then(() => {
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2500);
    });
}

function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}

function buildWaConfirmUrl() {
    let phone = (config.business_phone ?? '6281234567890').replace(/\D/g, '');
    if (phone.startsWith('0')) phone = '62' + phone.substring(1);
    const text = `Halo, saya ingin konfirmasi pesanan ${props.order.order_number}`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
}
</script>

<template>
    <PublicLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 pt-28 md:pt-36 pb-16 px-4">
            <div class="mx-auto max-w-4xl space-y-6">
                
                <!-- Success Header Card -->
                <section class="rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 md:p-8 shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-emerald-500"></div>
                    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-4 ring-emerald-500/10 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Pemesanan Berhasil</p>
                                <h1 class="mt-1 text-2xl md:text-3xl font-black text-gray-950 dark:text-white tracking-tight">{{ order.order_number }}</h1>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Terima kasih, <strong class="text-gray-800 dark:text-gray-200">{{ order.customer_name }}</strong>. Pesanan Anda telah diterima dan sedang menunggu verifikasi pembayaran.</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-start md:items-end gap-2 shrink-0">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                {{ order.status }}
                            </span>
                            <span v-if="order.payment_status" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider"
                                  :class="order.payment_status === 'paid' ? 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200'
                                        : order.payment_status === 'partial' ? 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200'
                                        : 'bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200'">
                                {{ order.payment_status === 'paid' ? 'Lunas' : order.payment_status === 'partial' ? 'DP Terbayar' : 'Belum Lunas' }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- Order Info Grid -->
                <section class="grid gap-6 md:grid-cols-12">
                    
                    <!-- Left: Invoice / Items Summary -->
                    <div class="md:col-span-7 xl:col-span-8 rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 md:p-8 shadow-sm space-y-6">
                        <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-800 pb-4">
                            <div>
                                <h2 class="text-base font-black text-gray-950 dark:text-white">Rincian Belanja</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dibuat pada {{ order.created_at_formatted }}</p>
                            </div>
                        </div>

                        <!-- Product Items List -->
                        <div class="divide-y divide-gray-150 dark:divide-gray-800">
                            <div
                                v-for="(item, i) in order.items"
                                :key="i"
                                class="flex items-center gap-4 py-4"
                            >
                                <div class="relative w-12 h-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img v-if="item.image" :src="item.image.startsWith('http') || item.image.startsWith('/') ? item.image : `/storage/${item.image}`" :alt="item.name" class="w-full h-full object-cover" />
                                    <svg v-else class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-white">{{ item.name }}</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ item.qty }} x {{ formatPrice(item.price) }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-extrabold text-gray-900 dark:text-white">{{ formatPrice(item.qty * item.price) }}</p>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-150 dark:border-gray-800 pt-5 space-y-3.5 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                                <span class="font-bold text-gray-950 dark:text-white">{{ formatPrice(order.subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Ongkos Kirim</span>
                                <span class="font-bold text-gray-950 dark:text-white">{{ order.shipping_cost > 0 ? formatPrice(order.shipping_cost) : 'Gratis' }}</span>
                            </div>

                            <div class="border-t border-gray-150 dark:border-gray-800 pt-4">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-gray-950 dark:text-white font-black">Total Pembayaran</span>
                                    <span class="text-xl font-black text-amber-600 dark:text-amber-400">{{ formatPrice(order.total) }}</span>
                                </div>
                            </div>

                            <!-- Partner DP Info -->
                            <div v-if="order.down_payment_amount > 0" class="bg-blue-50/50 dark:bg-blue-950/10 border border-blue-200 dark:border-blue-800/40 rounded-2xl p-4 mt-4 space-y-2">
                                <div class="flex justify-between text-xs font-semibold text-blue-700 dark:text-blue-400">
                                    <span>Uang Muka (DP) Dibayar:</span>
                                    <span class="font-extrabold">{{ formatPrice(order.down_payment_amount) }}</span>
                                </div>
                                <div class="flex justify-between text-xs font-semibold text-gray-600 dark:text-gray-400">
                                    <span>Sisa Piutang Pelunasan:</span>
                                    <span class="font-extrabold">{{ formatPrice(order.remaining_balance) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Shipping Details -->
                    <div class="md:col-span-5 xl:col-span-4 rounded-3xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 md:p-8 shadow-sm space-y-6">
                        <h2 class="text-base font-black text-gray-950 dark:text-white flex items-center gap-2 pb-3 border-b border-gray-150 dark:border-gray-800">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Pengiriman
                        </h2>
                        
                        <dl class="space-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Nama Penerima</dt>
                                <dd class="mt-1 font-bold text-gray-900 dark:text-white">{{ order.customer_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Nomor WhatsApp</dt>
                                <dd class="mt-1 font-bold text-gray-900 dark:text-white">{{ order.customer_phone }}</dd>
                            </div>
                            <div v-if="order.customer_email">
                                <dt class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Email</dt>
                                <dd class="mt-1 font-semibold text-gray-900 dark:text-white break-all">{{ order.customer_email }}</dd>
                            </div>
                            <div v-if="order.customer_address">
                                <dt class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Alamat Lengkap</dt>
                                <dd class="mt-1 leading-relaxed text-gray-700 dark:text-gray-300 text-xs font-medium">{{ order.customer_address }}</dd>
                            </div>
                            <div v-if="order.notes">
                                <dt class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">Catatan Tambahan</dt>
                                <dd class="mt-1 italic text-gray-600 dark:text-gray-400 text-xs bg-gray-50 dark:bg-gray-800 p-2.5 rounded-lg border border-gray-150 dark:border-gray-700/50">{{ order.notes }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <!-- Payment Instructions -->
                <section
                    v-if="order.payment_method_detail && order.payment_method !== 'cod'"
                    class="rounded-3xl border border-amber-200 dark:border-amber-900/30 bg-amber-50/20 dark:bg-amber-950/10 p-6 md:p-8 shadow-sm space-y-6"
                >
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-amber-200/50 dark:border-amber-900/30 pb-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Instruksi Pembayaran</p>
                            <h2 class="mt-1 text-xl font-black text-gray-950 dark:text-white">{{ order.payment_method_detail.name }}</h2>
                        </div>
                        <div class="rounded-2xl bg-white dark:bg-gray-900 px-4 py-3 text-left md:text-right ring-1 ring-amber-200/50 dark:ring-amber-900/30 shadow-inner">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Total yang Harus Ditransfer</p>
                            <p class="text-xl font-black text-amber-600 dark:text-amber-400 mt-0.5">
                                {{ order.down_payment_amount > 0 ? formatPrice(order.down_payment_amount) : formatPrice(order.total) }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div v-if="order.payment_method_detail.account_number" class="rounded-2xl bg-white dark:bg-gray-900 p-4 ring-1 ring-gray-150 dark:ring-gray-800 shadow-sm">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Nomor Rekening / Telepon</p>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="break-all text-lg font-black text-gray-950 dark:text-white tracking-wide">{{ order.payment_method_detail.account_number }}</p>
                                <button
                                    type="button"
                                    @click="copyAccountNumber(order.payment_method_detail.account_number)"
                                    class="shrink-0 rounded-xl border border-gray-200 dark:border-gray-700 px-3.5 py-1.5 text-xs font-black text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.97]"
                                >
                                    {{ copied ? 'Tersalin' : 'Salin' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="order.payment_method_detail.account_name" class="rounded-2xl bg-white dark:bg-gray-900 p-4 ring-1 ring-gray-150 dark:ring-gray-800 shadow-sm">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Atas Nama Pemilik</p>
                            <p class="mt-2 text-base font-bold text-gray-950 dark:text-white">{{ order.payment_method_detail.account_name }}</p>
                        </div>

                        <div v-if="order.payment_method_detail.description" class="rounded-2xl bg-white dark:bg-gray-900 p-4 ring-1 ring-gray-150 sm:col-span-2 dark:ring-gray-800 shadow-sm">
                            <p class="text-xs font-black text-gray-400 uppercase tracking-wider">Langkah/Keterangan Transfer</p>
                            <div class="rich-text-content mt-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300 font-medium" v-html="order.payment_method_detail.description"></div>
                        </div>
                    </div>

                    <div class="bg-amber-100/50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/30 rounded-2xl p-4 text-xs font-medium text-amber-800 dark:text-amber-300 leading-relaxed">
                        Lakukan pembayaran dalam 24 jam ke depan. Setelah berhasil transfer, harap simpan bukti transfer Anda dan klik tombol <strong>"Konfirmasi via WhatsApp"</strong> di bawah untuk mempercepat validasi pesanan Anda oleh admin kami.
                    </div>
                </section>

                <section
                    v-else-if="order.payment_method === 'cod'"
                    class="rounded-3xl border border-emerald-200 dark:border-emerald-900/30 bg-emerald-50/20 dark:bg-emerald-950/10 p-6 md:p-8 shadow-sm"
                >
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-md">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-gray-950 dark:text-white">Bayar di Tempat (COD)</h2>
                            <p class="mt-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300 font-medium">Pesanan Anda akan dikemas dan dikirimkan oleh kurir kami. Mohon persiapkan uang tunai sebesar <strong class="text-amber-600">{{ formatPrice(order.total) }}</strong> saat paket diserahkan ke alamat Anda.</p>
                        </div>
                    </div>
                </section>

                <!-- Navigation & Action Buttons -->
                <div class="flex flex-col gap-4 sm:flex-row pt-4">
                    <a
                        :href="config.catalog_url ?? '/'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-gray-250 dark:border-gray-800 bg-white dark:bg-gray-900 px-5 py-4 text-sm font-black text-gray-700 dark:text-gray-300 shadow-sm transition hover:bg-gray-50 dark:hover:bg-gray-850 active:scale-[0.98]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali Belanja
                    </a>
                    <Link
                        v-if="page.props.auth?.user"
                        :href="route('my-orders.index')"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl border border-amber-200 dark:border-amber-900/30 bg-amber-50/30 dark:bg-amber-950/10 px-5 py-4 text-sm font-black text-amber-700 dark:text-amber-400 shadow-sm transition hover:bg-amber-100/30 dark:hover:bg-amber-950/30 active:scale-[0.98]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Riwayat Pesanan
                    </Link>
                    <a
                        :href="buildWaConfirmUrl()"
                        target="_blank"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-2xl bg-[#25D366] hover:bg-[#20ba5a] px-5 py-4 text-sm font-black text-white shadow-md transition active:scale-[0.98]"
                    >
                        <svg class="h-4.5 w-4.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Konfirmasi Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
