<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';

const props = defineProps({
    orders: { type: Object, required: true },
});

// orders.data, orders.links, orders.meta.current_page, orders.meta.last_page, etc.

function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

const totalCount = computed(() => props.orders?.meta?.total ?? props.orders?.data?.length ?? 0);

// ── Status order ──────────────────────────────────────────────────────────────
const orderStatusConfig = {
    pending:    { label: 'Pending',      classes: 'bg-gray-100 text-gray-700 ring-gray-300 dark:bg-gray-700/40 dark:text-gray-300 dark:ring-gray-600' },
    confirmed:  { label: 'Dikonfirmasi', classes: 'bg-blue-100 text-blue-700 ring-blue-300 dark:bg-blue-500/20 dark:text-blue-300 dark:ring-blue-500/30' },
    processing: { label: 'Diproses',     classes: 'bg-indigo-100 text-indigo-700 ring-indigo-300 dark:bg-indigo-500/20 dark:text-indigo-300 dark:ring-indigo-500/30' },
    shipped:    { label: 'Dikirim',      classes: 'bg-cyan-100 text-cyan-700 ring-cyan-300 dark:bg-cyan-500/20 dark:text-cyan-300 dark:ring-cyan-500/30' },
    completed:  { label: 'Selesai',      classes: 'bg-green-100 text-green-700 ring-green-300 dark:bg-green-500/20 dark:text-green-300 dark:ring-green-500/30' },
    cancelled:  { label: 'Dibatalkan',   classes: 'bg-red-100 text-red-700 ring-red-300 dark:bg-red-500/20 dark:text-red-300 dark:ring-red-500/30' },
};

function orderStatusBadge(status) {
    return orderStatusConfig[status] ?? { label: status, classes: 'bg-gray-100 text-gray-600 ring-gray-300 dark:bg-gray-700/40 dark:text-gray-400 dark:ring-gray-600' };
}

// ── Status pembayaran ─────────────────────────────────────────────────────────
const paymentStatusConfig = {
    unpaid:   { label: 'Belum Bayar', classes: 'bg-red-100 text-red-700 ring-red-300 dark:bg-red-500/20 dark:text-red-300 dark:ring-red-500/30' },
    paid:     { label: 'Lunas',       classes: 'bg-green-100 text-green-700 ring-green-300 dark:bg-green-500/20 dark:text-green-300 dark:ring-green-500/30' },
    partial:  { label: 'DP',          classes: 'bg-amber-100 text-amber-700 ring-amber-300 dark:bg-amber-500/20 dark:text-amber-300 dark:ring-amber-500/30' },
    refunded: { label: 'Dikembalikan',classes: 'bg-purple-100 text-purple-700 ring-purple-300 dark:bg-purple-500/20 dark:text-purple-300 dark:ring-purple-500/30' },
};

function paymentStatusBadge(status) {
    return paymentStatusConfig[status] ?? { label: status, classes: 'bg-gray-100 text-gray-600 ring-gray-300 dark:bg-gray-700/40 dark:text-gray-400 dark:ring-gray-600' };
}

// ── Pagination links helper ───────────────────────────────────────────────────
// Filter out "Previous" / "Next" text links, keep only numbered pages
const pageLinks = computed(() => {
    const links = props.orders?.links ?? props.orders?.meta?.links ?? [];
    return links;
});
</script>

<template>
    <PublicLayout>
        <div class="min-h-screen bg-gray-50 px-4 py-10 dark:bg-gray-950">
            <div class="mx-auto max-w-6xl space-y-6">

                <!-- ── Page header ──────────────────────────────────────────── -->
                <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-950 dark:text-white">Riwayat Pesanan Saya</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Total {{ totalCount }} pesanan
                        </p>
                    </div>
                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Mulai Belanja
                    </Link>
                </div>

                <!-- ── Empty state ─────────────────────────────────────────── -->
                <div
                    v-if="!orders.data || orders.data.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white px-8 py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:ring-amber-500/20">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Belum ada pesanan</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Yuk mulai berbelanja dan temukan produk kerajinan terbaik.</p>
                    <Link
                        href="/"
                        class="mt-6 inline-flex items-center gap-2 rounded-lg bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Mulai Belanja
                    </Link>
                </div>

                <!-- ── Orders table ────────────────────────────────────────── -->
                <template v-else>

                    <!-- Desktop table -->
                    <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 md:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/60">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Pesanan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jumlah Item</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status Pesanan</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status Pembayaran</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Resi</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="transition hover:bg-gray-50 dark:hover:bg-gray-800/40"
                                >
                                    <!-- Nomor Pesanan -->
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ order.order_number }}</span>
                                    </td>

                                    <!-- Tanggal -->
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </td>

                                    <!-- Jumlah Item -->
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex h-6 min-w-[1.5rem] items-center justify-center rounded-full bg-gray-100 px-2 text-xs font-bold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ Array.isArray(order.items) ? order.items.length : (order.items_count ?? '-') }}
                                        </span>
                                    </td>

                                    <!-- Total -->
                                    <td class="px-4 py-3 text-right text-sm font-bold text-amber-600 dark:text-amber-400">
                                        {{ formatPrice(order.total) }}
                                    </td>

                                    <!-- Status Pesanan -->
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ring-1"
                                            :class="orderStatusBadge(order.status).classes"
                                        >
                                            {{ orderStatusBadge(order.status).label }}
                                        </span>
                                    </td>

                                    <!-- Status Pembayaran -->
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold ring-1"
                                            :class="paymentStatusBadge(order.payment_status).classes"
                                        >
                                            {{ paymentStatusBadge(order.payment_status).label }}
                                        </span>
                                    </td>

                                    <!-- Nomor Resi -->
                                    <td class="px-4 py-3">
                                        <span
                                            v-if="order.tracking_number"
                                            class="inline-flex items-center gap-1 rounded-md bg-cyan-50 px-2 py-0.5 font-mono text-xs font-semibold text-cyan-700 ring-1 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-300 dark:ring-cyan-500/20"
                                        >
                                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2 2-2h6l2 2 2-2"/>
                                            </svg>
                                            {{ order.tracking_number }}
                                        </span>
                                        <span v-else class="text-xs text-gray-400 dark:text-gray-600">—</span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="px-4 py-3 text-center">
                                        <Link
                                            :href="`/my-orders/${order.order_number}`"
                                            class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                        >
                                            Detail
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile card list -->
                    <div class="space-y-3 md:hidden">
                        <div
                            v-for="order in orders.data"
                            :key="order.id"
                            class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900"
                        >
                            <!-- Header row -->
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ order.order_number }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ formatDate(order.created_at) }}</p>
                                </div>
                                <span
                                    class="inline-flex shrink-0 items-center rounded-full px-2.5 py-0.5 text-xs font-bold ring-1"
                                    :class="orderStatusBadge(order.status).classes"
                                >
                                    {{ orderStatusBadge(order.status).label }}
                                </span>
                            </div>

                            <!-- Info row -->
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ formatPrice(order.total) }}</span>
                                <span class="text-xs text-gray-400">·</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ Array.isArray(order.items) ? order.items.length : (order.items_count ?? '-') }} item
                                </span>
                                <span class="text-xs text-gray-400">·</span>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold ring-1"
                                    :class="paymentStatusBadge(order.payment_status).classes"
                                >
                                    {{ paymentStatusBadge(order.payment_status).label }}
                                </span>
                            </div>

                            <!-- Tracking -->
                            <div v-if="order.tracking_number" class="mt-2">
                                <span class="inline-flex items-center gap-1 rounded-md bg-cyan-50 px-2 py-0.5 font-mono text-xs font-semibold text-cyan-700 ring-1 ring-cyan-200 dark:bg-cyan-500/10 dark:text-cyan-300 dark:ring-cyan-500/20">
                                    <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 2 2-2h6l2 2 2-2"/>
                                    </svg>
                                    {{ order.tracking_number }}
                                </span>
                            </div>

                            <!-- Action -->
                            <div class="mt-3 border-t border-gray-100 pt-3 dark:border-gray-800">
                                <Link
                                    :href="`/my-orders/${order.order_number}`"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    Lihat Detail
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- ── Pagination ──────────────────────────────────────── -->
                    <div
                        v-if="pageLinks && pageLinks.length > 3"
                        class="flex flex-wrap items-center justify-center gap-1"
                    >
                        <template v-for="(link, idx) in pageLinks" :key="idx">
                            <!-- Previous -->
                            <Link
                                v-if="idx === 0"
                                :href="link.url ?? '#'"
                                :class="[
                                    'inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm font-semibold shadow-sm transition',
                                    link.url
                                        ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800'
                                        : 'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-600',
                                ]"
                                :as="link.url ? 'a' : 'span'"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Prev
                            </Link>

                            <!-- Numbered pages -->
                            <Link
                                v-else-if="idx !== pageLinks.length - 1"
                                :href="link.url ?? '#'"
                                :class="[
                                    'inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border px-3 text-sm font-semibold shadow-sm transition',
                                    link.active
                                        ? 'border-amber-500 bg-amber-500 text-white shadow-amber-200 dark:shadow-amber-900/30'
                                        : link.url
                                            ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800'
                                            : 'cursor-default border-gray-100 bg-gray-50 text-gray-400 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-600',
                                ]"
                                :as="link.url ? 'a' : 'span'"
                                v-html="link.label"
                            />

                            <!-- Next -->
                            <Link
                                v-else
                                :href="link.url ?? '#'"
                                :class="[
                                    'inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-sm font-semibold shadow-sm transition',
                                    link.url
                                        ? 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800'
                                        : 'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-600',
                                ]"
                                :as="link.url ? 'a' : 'span'"
                            >
                                Next
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </template>
                    </div>

                </template>

            </div>
        </div>
    </PublicLayout>
</template>
