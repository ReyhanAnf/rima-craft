<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

const props = defineProps({
    order: { type: Object, required: true },
});

// ── Helpers ──────────────────────────────────────────────────────────────────

function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}

function formatDate(val) {
    if (!val) return '-';
    return new Date(val).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatDateShort(val) {
    if (!val) return null;
    return new Date(val).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

// ── Status badge ─────────────────────────────────────────────────────────────

function statusBadgeClass(status) {
    const map = {
        pending:    'bg-gray-100 text-gray-700 ring-gray-200 dark:bg-gray-700/30 dark:text-gray-300 dark:ring-gray-600',
        confirmed:  'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/20',
        processing: 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-500/10 dark:text-violet-300 dark:ring-violet-500/20',
        shipped:    'bg-cyan-50 text-cyan-700 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-300 dark:ring-cyan-500/20',
        completed:  'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
        cancelled:  'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/20',
    };
    return map[status] ?? map.pending;
}

function statusLabel(status) {
    const map = {
        pending:    'Menunggu',
        confirmed:  'Dikonfirmasi',
        processing: 'Diproses',
        shipped:    'Dikirim',
        completed:  'Selesai',
        cancelled:  'Dibatalkan',
    };
    return map[status] ?? status;
}

// ── Clipboard ─────────────────────────────────────────────────────────────────

const copiedResi = ref(false);

function copyResi() {
    navigator.clipboard.writeText(props.order.tracking_number ?? '').then(() => {
        copiedResi.value = true;
        setTimeout(() => { copiedResi.value = false; }, 2500);
    });
}

// ── WA Confirmation URL ───────────────────────────────────────────────────────

const page = usePage();

const waConfirmUrl = computed(() => {
    const config = page.props.siteConfig ?? {};
    let phone = (config.business_phone ?? '6281234567890').replace(/\D/g, '');
    if (phone.startsWith('0')) phone = '62' + phone.substring(1);
    const text = `Halo, saya ingin konfirmasi pembayaran pesanan ${props.order.order_number} atas nama ${props.order.customer_name}.`;
    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
});

// ── Timeline steps ────────────────────────────────────────────────────────────

const timelineSteps = [
    { key: 'created',   label: 'Dibuat',       timestampField: 'created_at' },
    { key: 'confirmed', label: 'Dikonfirmasi',  timestampField: 'confirmed_at' },
    { key: 'processing',label: 'Diproses',      timestampField: null },
    { key: 'shipped',   label: 'Dikirim',       timestampField: 'shipped_at' },
    { key: 'completed', label: 'Selesai',        timestampField: 'completed_at' },
];

const statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'completed'];

function stepActive(stepKey) {
    if (props.order.status === 'cancelled') return stepKey === 'created';
    const currentIdx = statusOrder.indexOf(props.order.status);
    const stepIdx    = statusOrder.indexOf(stepKey);
    return stepIdx <= currentIdx;
}

function stepTimestamp(step) {
    if (!step.timestampField) {
        // "processing" step: active when status is at least processing, no separate timestamp
        return null;
    }
    const val = props.order[step.timestampField];
    return val ? formatDateShort(val) : null;
}

const processing = ref(false);

function completeOrder() {
    if (confirm('Apakah Anda yakin barang pesanan ini sudah diterima dengan baik?')) {
        processing.value = true;
        router.patch(route('my-orders.complete', props.order.order_number), {}, {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
            }
        });
    }
}
</script>

<template>
    <AdminLayout>
        <div class="py-6 space-y-6">
            <div class="mx-auto max-w-3xl space-y-4">

                <!-- ── Section 1: Header ──────────────────────────────────── -->
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <Link
                        :href="route('my-orders.index')"
                                class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 transition-colors"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Kembali ke Pesanan Saya
                            </Link>
                            <h1 class="mt-2 text-xl font-bold text-gray-950 dark:text-white">{{ order.order_number }}</h1>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>
                        <span
                            class="inline-flex w-fit items-center rounded-full px-2.5 py-1 text-xs font-bold capitalize ring-1"
                            :class="statusBadgeClass(order.status)"
                        >
                            {{ statusLabel(order.status) }}
                        </span>
                    </div>
                </section>

                <!-- ── Section 2: Order Items ─────────────────────────────── -->
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-sm font-bold text-gray-950 dark:text-white mb-3">Item Pesanan</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="pb-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Produk</th>
                                    <th class="pb-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Harga Satuan</th>
                                    <th class="pb-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr v-for="(item, i) in order.items" :key="i">
                                    <td class="py-2.5 pr-4 font-semibold text-gray-900 dark:text-white">{{ item.name }}</td>
                                    <td class="py-2.5 text-center text-gray-600 dark:text-gray-300">{{ item.qty }}</td>
                                    <td class="py-2.5 text-right text-gray-600 dark:text-gray-300">{{ formatPrice(item.price) }}</td>
                                    <td class="py-2.5 text-right font-bold text-gray-900 dark:text-white">{{ formatPrice(item.qty * item.price) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Grand total -->
                    <div class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Grand Total</span>
                            <span class="text-lg font-black text-amber-600 dark:text-amber-400">{{ formatPrice(order.total) }}</span>
                        </div>
                    </div>
                </section>

                <!-- ── Section 3: Payment Summary ─────────────────────────── -->
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-sm font-bold text-gray-950 dark:text-white mb-3">Ringkasan Pembayaran</h2>

                    <dl class="space-y-2 text-sm">
                        <!-- Total -->
                        <div class="flex items-center justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Total</dt>
                            <dd class="font-bold text-gray-900 dark:text-white">{{ formatPrice(order.total) }}</dd>
                        </div>

                        <!-- DP rows — only when down_payment_amount > 0 -->
                        <template v-if="Number(order.down_payment_amount) > 0">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">DP Dibayar</dt>
                                <dd class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatPrice(order.down_payment_amount) }}</dd>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/20">
                                <dt class="font-semibold text-amber-800 dark:text-amber-300">Sisa Piutang</dt>
                                <dd class="font-black text-amber-700 dark:text-amber-400">{{ formatPrice(order.remaining_balance) }}</dd>
                            </div>
                        </template>

                        <!-- Metode pembayaran -->
                        <div class="flex items-center justify-between border-t border-gray-100 pt-2 dark:border-gray-800">
                            <dt class="text-gray-500 dark:text-gray-400">Metode Pembayaran</dt>
                            <dd class="font-semibold capitalize text-gray-900 dark:text-white">
                                {{ order.payment_method ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </section>

                <!-- ── Section 4: Tracking / Nomor Resi ──────────────────── -->
                <section
                    v-if="order.tracking_number"
                    class="rounded-xl border border-cyan-200 bg-cyan-50/70 p-4 shadow-sm dark:border-cyan-500/20 dark:bg-cyan-500/10"
                >
                    <div class="flex items-center gap-2 mb-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-600 text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-gray-950 dark:text-white">Nomor Resi</h2>
                    </div>

                    <div class="flex items-center justify-between gap-3 rounded-lg bg-white px-3 py-2.5 ring-1 ring-cyan-200 dark:bg-gray-900 dark:ring-cyan-500/20">
                        <span class="font-mono text-lg font-bold tracking-wider text-gray-950 dark:text-white break-all">
                            {{ order.tracking_number }}
                        </span>
                        <button
                            type="button"
                            @click="copyResi"
                            class="shrink-0 rounded-md border border-gray-200 px-2.5 py-1 text-xs font-bold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800"
                        >
                            {{ copiedResi ? 'Tersalin ✓' : 'Salin' }}
                        </button>
                    </div>

                    <a
                        :href="`https://www.google.com/search?q=${encodeURIComponent(order.tracking_number + ' cek resi')}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-cyan-700 hover:text-cyan-800 dark:text-cyan-400 dark:hover:text-cyan-300 transition-colors"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Cek resi di Google
                    </a>
                </section>

                <!-- ── Section 5: Status Timeline ─────────────────────────── -->
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-sm font-bold text-gray-950 dark:text-white mb-4">Status Pesanan</h2>

                    <!-- Cancelled state -->
                    <div
                        v-if="order.status === 'cancelled'"
                        class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-500/20 dark:bg-red-500/10"
                    >
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-600 text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-red-700 dark:text-red-300">Dibatalkan</p>
                                <p v-if="order.cancelled_at" class="mt-0.5 text-xs text-red-600 dark:text-red-400">
                                    {{ formatDateShort(order.cancelled_at) }}
                                </p>
                                <p v-if="order.cancellation_reason" class="mt-1 text-xs text-gray-700 dark:text-gray-300">
                                    Alasan: {{ order.cancellation_reason }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Normal timeline -->
                    <div v-else class="relative">
                        <!-- Horizontal connector line (desktop) -->
                        <div class="hidden sm:block absolute top-4 left-0 right-0 h-0.5 bg-gray-200 dark:bg-gray-700" style="left: 2rem; right: 2rem;" />

                        <ol class="flex flex-col sm:flex-row sm:justify-between gap-4 sm:gap-0 relative">
                            <li
                                v-for="step in timelineSteps"
                                :key="step.key"
                                class="flex sm:flex-col items-center sm:items-center gap-3 sm:gap-0 sm:flex-1"
                            >
                                <!-- Circle -->
                                <div
                                    class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-2 transition-colors"
                                    :class="stepActive(step.key)
                                        ? 'bg-amber-500 ring-amber-300 dark:bg-amber-500 dark:ring-amber-500/40'
                                        : 'bg-gray-100 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700'"
                                >
                                    <svg
                                        v-if="stepActive(step.key)"
                                        class="h-4 w-4 text-white"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span
                                        v-else
                                        class="h-2 w-2 rounded-full bg-gray-300 dark:bg-gray-600"
                                    />
                                </div>

                                <!-- Label & timestamp -->
                                <div class="sm:mt-2 sm:text-center">
                                    <p
                                        class="text-xs font-semibold"
                                        :class="stepActive(step.key)
                                            ? 'text-amber-600 dark:text-amber-400'
                                            : 'text-gray-400 dark:text-gray-600'"
                                    >
                                        {{ step.label }}
                                    </p>
                                    <p
                                        v-if="stepTimestamp(step)"
                                        class="mt-0.5 text-[10px] text-gray-500 dark:text-gray-400"
                                    >
                                        {{ stepTimestamp(step) }}
                                    </p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <!-- Complete Order CTA for User -->
                    <div v-if="order.status === 'shipped'" class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800 text-center space-y-3">
                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium">
                            Barang Anda sedang dalam pengiriman dengan Nomor Resi di atas. Jika barang sudah sampai di tangan Anda dengan selamat, silakan konfirmasi penerimaan pesanan di bawah ini.
                        </p>
                        <button
                            type="button"
                            @click="completeOrder"
                            :disabled="processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700 text-white font-bold text-xs px-6 py-3 transition shadow-sm disabled:opacity-50"
                        >
                            <svg v-if="processing" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Konfirmasi Barang Diterima
                        </button>
                    </div>
                </section>

                <!-- ── Section 6: Shipping Info ───────────────────────────── -->
                <section class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-sm font-bold text-gray-950 dark:text-white mb-3">Info Pengiriman</h2>

                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Nama</dt>
                            <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ order.customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Telepon</dt>
                            <dd class="mt-0.5 font-semibold text-gray-900 dark:text-white">{{ order.customer_phone }}</dd>
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
                </section>

                <!-- ── Back button ────────────────────────────────────────── -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <a
                        v-if="order.payment_status !== 'paid'"
                        :href="waConfirmUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] hover:bg-[#20ba5a] px-4 py-3 text-sm font-bold text-white shadow-sm transition active:scale-[0.97]"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Konfirmasi Pembayaran via WhatsApp
                    </a>
                    <Link
                        :href="route('my-orders.index')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Pesanan Saya
                    </Link>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
