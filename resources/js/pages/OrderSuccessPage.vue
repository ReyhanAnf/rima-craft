<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
        <div class="min-h-screen bg-gray-50 px-4 py-6 dark:bg-gray-950">
            <div class="mx-auto max-w-3xl space-y-4">
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400">Pesanan berhasil dibuat</p>
                                <h1 class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ order.order_number }}</h1>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Terima kasih, {{ order.customer_name }}. Simpan nomor pesanan ini untuk konfirmasi.</p>
                            </div>
                        </div>
                        <span class="inline-flex w-fit items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold capitalize text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                            {{ order.status }}
                        </span>
                    </div>
                </section>

                <section class="grid gap-4 lg:grid-cols-[1fr_280px]">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                            <div>
                                <h2 class="text-sm font-bold text-gray-950 dark:text-white">Ringkasan Pesanan</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ order.created_at_formatted }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                                <p class="text-lg font-black text-amber-600 dark:text-amber-400">{{ formatPrice(order.total) }}</p>
                            </div>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-800">
                            <div
                                v-for="(item, i) in order.items"
                                :key="i"
                                class="flex items-start justify-between gap-4 py-3"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ item.name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ item.qty }} x {{ formatPrice(item.price) }}</p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(item.qty * item.price) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-sm font-bold text-gray-950 dark:text-white">Pengiriman</h2>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Nama</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ order.customer_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Telepon</dt>
                                <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ order.customer_phone }}</dd>
                            </div>
                            <div v-if="order.customer_email">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Email</dt>
                                <dd class="mt-0.5 break-words font-semibold text-gray-900 dark:text-white">{{ order.customer_email }}</dd>
                            </div>
                            <div v-if="order.customer_address">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Alamat</dt>
                                <dd class="mt-0.5 leading-relaxed text-gray-700 dark:text-gray-300">{{ order.customer_address }}</dd>
                            </div>
                            <div v-if="order.notes">
                                <dt class="text-xs text-gray-500 dark:text-gray-400">Catatan</dt>
                                <dd class="mt-0.5 italic text-gray-600 dark:text-gray-400">{{ order.notes }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section
                    v-if="order.payment_method_detail && order.payment_method !== 'cod'"
                    class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10"
                >
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Instruksi Pembayaran</p>
                            <h2 class="mt-1 text-base font-bold text-gray-950 dark:text-white">{{ order.payment_method_detail.name }}</h2>
                        </div>
                        <div class="rounded-lg bg-white px-3 py-2 text-right ring-1 ring-amber-200 dark:bg-gray-900 dark:ring-amber-500/20">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total pembayaran</p>
                            <p class="font-black text-amber-600 dark:text-amber-400">{{ formatPrice(order.total) }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div v-if="order.payment_method_detail.account_number" class="rounded-lg bg-white p-3 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nomor Rekening / Telepon</p>
                            <div class="mt-1 flex items-center justify-between gap-3">
                                <p class="break-all text-base font-bold text-gray-950 dark:text-white">{{ order.payment_method_detail.account_number }}</p>
                                <button
                                    type="button"
                                    @click="copyAccountNumber(order.payment_method_detail.account_number)"
                                    class="shrink-0 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                                >
                                    {{ copied ? 'Tersalin' : 'Salin' }}
                                </button>
                            </div>
                        </div>

                        <div v-if="order.payment_method_detail.account_name" class="rounded-lg bg-white p-3 ring-1 ring-gray-200 dark:bg-gray-900 dark:ring-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atas Nama</p>
                            <p class="mt-1 font-semibold text-gray-950 dark:text-white">{{ order.payment_method_detail.account_name }}</p>
                        </div>

                        <div v-if="order.payment_method_detail.description" class="rounded-lg bg-white p-3 ring-1 ring-gray-200 sm:col-span-2 dark:bg-gray-900 dark:ring-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Keterangan</p>
                            <p class="mt-1 text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ order.payment_method_detail.description }}</p>
                        </div>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-amber-800 dark:text-amber-200">
                        Lakukan pembayaran dalam 24 jam. Setelah transfer, konfirmasi melalui WhatsApp agar pesanan segera diproses.
                    </p>
                </section>

                <section
                    v-else-if="order.payment_method === 'cod'"
                    class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 shadow-sm dark:border-emerald-500/20 dark:bg-emerald-500/10"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-gray-950 dark:text-white">Bayar di Tempat (COD)</h2>
                            <p class="mt-1 text-sm leading-relaxed text-gray-700 dark:text-gray-300">Pesanan akan dikirim ke alamat yang diberikan. Siapkan pembayaran saat barang diterima.</p>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a
                        :href="config.catalog_url ?? '/'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Lanjutkan Belanja
                    </a>
                    <a
                        :href="buildWaConfirmUrl()"
                        target="_blank"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-[#25D366] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#20ba5a]"
                    >
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Konfirmasi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
