<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    paymentMethods: { type: Array, default: () => [] },
    errors:         { type: Object, default: () => ({}) },
    isGuest:        { type: Boolean, default: true },
    user:           { type: Object, default: null },
});

const page   = usePage();
const config = computed(() => page.props.siteConfig ?? {});
const cart   = useCartStore();
const toast  = useToastStore();

// Group payment methods by type
const groupedMethods = computed(() => {
    const groups = {};
    for (const m of props.paymentMethods) {
        if (!groups[m.type]) groups[m.type] = [];
        groups[m.type].push(m);
    }
    return groups;
});

const typeLabel = {
    bank:    'Transfer Bank',
    ewallet: 'E-Wallet',
    qris:    'QRIS',
    cod:     'Bayar di Tempat',
};

const form = useForm({
    customer_name:    props.user?.name    ?? '',
    customer_email:   props.user?.email   ?? '',
    customer_phone:   props.user?.phone   ?? '',
    customer_address: '',
    payment_method:   '',
    notes:            cart.notes,
    create_account:   false,
    password:         '',
    password_confirmation: '',
    order_method:     'form',
    subtotal:         0,
    shipping_cost:    0,
    total:            0,
    items:            '[]',
});

const submitting = ref(false);

const canSubmit = computed(() =>
    cart.items.length > 0 && form.payment_method !== ''
);

function submit() {
    if (!canSubmit.value) return;

    form.subtotal  = cart.totalPrice;
    form.total     = cart.totalPrice;
    form.items     = JSON.stringify(cart.items);

    submitting.value = true;

    form.post(config.value.order_store_url ?? '/pesanan', {
        onError: () => {
            submitting.value = false;
            toast.error('Ada kesalahan pada form. Periksa kembali data Anda.');
        },
        onFinish: () => {
            submitting.value = false;
        },
    });
}

function formatPrice(val) {
    const n = Number(val);
    if (isNaN(n)) return 'Rp -';
    return 'Rp ' + n.toLocaleString('id-ID');
}
</script>

<template>
    <PublicLayout :config="config">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">Form Pemesanan</h1>
                    <p class="text-gray-600 dark:text-gray-400">Lengkapi data di bawah untuk memproses pesanan Anda</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left: Order Form -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 md:p-8 shadow-sm">

                            <!-- Server-side errors -->
                            <div v-if="Object.keys(errors).length" class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm border border-red-200 dark:border-red-800/50">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li v-for="(msg, field) in errors" :key="field">{{ msg }}</li>
                                </ul>
                            </div>

                            <form @submit.prevent="submit" class="space-y-6">

                                <!-- Customer Info -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informasi Pelanggan</h3>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                Nama Lengkap <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="form.customer_name"
                                                type="text" required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                placeholder="Masukkan nama lengkap"
                                            />
                                            <p v-if="form.errors.customer_name" class="mt-1 text-xs text-red-500">{{ form.errors.customer_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                No. WhatsApp <span class="text-red-500">*</span>
                                            </label>
                                            <input
                                                v-model="form.customer_phone"
                                                type="tel" required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                placeholder="081234567890"
                                            />
                                            <p v-if="form.errors.customer_phone" class="mt-1 text-xs text-red-500">{{ form.errors.customer_phone }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            v-model="form.customer_email"
                                            type="email" required
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                            placeholder="nama@email.com"
                                        />
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email akan digunakan untuk login jika Anda membuat akun</p>
                                        <p v-if="form.errors.customer_email" class="mt-1 text-xs text-red-500">{{ form.errors.customer_email }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Alamat Pengiriman <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="form.customer_address"
                                            required rows="3"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                            placeholder="Masukkan alamat lengkap untuk pengiriman"
                                        />
                                        <p v-if="form.errors.customer_address" class="mt-1 text-xs text-red-500">{{ form.errors.customer_address }}</p>
                                    </div>
                                </div>

                                <!-- Create Account (guest only) -->
                                <div v-if="isGuest" class="bg-gradient-to-r from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/10 border border-amber-200 dark:border-amber-800/50 rounded-xl p-5">
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input
                                            v-model="form.create_account"
                                            type="checkbox"
                                            class="mt-1 w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500"
                                        />
                                        <div class="flex-1">
                                            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Buat Akun Sekarang</p>
                                            <p class="text-xs text-gray-600 dark:text-gray-400">Dapatkan akses untuk melacak pesanan dan melihat riwayat pembelian Anda</p>
                                        </div>
                                    </label>

                                    <Transition name="expand">
                                        <div v-if="form.create_account" class="mt-4 space-y-4 pt-4 border-t border-amber-200 dark:border-amber-800/50">
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Password <span class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    v-model="form.password"
                                                    type="password"
                                                    :required="form.create_account"
                                                    minlength="8"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                    placeholder="Minimal 8 karakter"
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                                    Konfirmasi Password <span class="text-red-500">*</span>
                                                </label>
                                                <input
                                                    v-model="form.password_confirmation"
                                                    type="password"
                                                    :required="form.create_account"
                                                    minlength="8"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                    placeholder="Ulangi password"
                                                />
                                            </div>
                                        </div>
                                    </Transition>
                                </div>

                                <!-- Payment Methods -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Metode Pembayaran</h3>

                                    <div class="grid grid-cols-1 gap-4">
                                        <div v-for="(methods, type) in groupedMethods" :key="type">
                                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                                {{ typeLabel[type] ?? type }}
                                            </p>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <label
                                                    v-for="method in methods"
                                                    :key="method.code"
                                                    class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-amber-300 dark:hover:border-amber-600"
                                                    :class="form.payment_method === method.code
                                                        ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                                                        : 'border-gray-200 dark:border-gray-700'"
                                                >
                                                    <input
                                                        v-model="form.payment_method"
                                                        type="radio"
                                                        :value="method.code"
                                                        required
                                                        class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500"
                                                    />
                                                    <div class="ml-3 flex-1">
                                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ method.name }}</p>
                                                        <p v-if="method.account_number" class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ method.account_number }} - {{ method.account_name }}
                                                        </p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.payment_method" class="text-xs text-red-500">{{ form.errors.payment_method }}</p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Catatan Pesanan (Opsional)
                                    </label>
                                    <textarea
                                        v-model="form.notes"
                                        rows="2"
                                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                        placeholder="Request khusus, instruksi pengiriman, dll"
                                    />
                                </div>

                                <!-- Submit -->
                                <button
                                    type="submit"
                                    :disabled="submitting || !canSubmit"
                                    class="w-full py-4 px-6 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-gray-950 rounded-xl font-bold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ submitting ? 'Memproses...' : 'Buat Pesanan' }}
                                </button>

                                <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                                    Setelah membuat pesanan, Anda akan diarahkan ke halaman konfirmasi dengan instruksi pembayaran
                                </p>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm sticky top-24">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ringkasan Pesanan</h3>

                            <div class="space-y-3 mb-6">
                                <!-- Empty state -->
                                <div v-if="cart.items.length === 0" class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Keranjang masih kosong</p>
                                    <a :href="config.catalog_url ?? '/'" class="inline-block mt-3 text-sm text-amber-600 hover:text-amber-700 font-medium">
                                        Mulai Belanja →
                                    </a>
                                </div>

                                <!-- Items -->
                                <div
                                    v-for="item in cart.items"
                                    :key="item.id"
                                    class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                                >
                                    <div class="flex-shrink-0 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-bold text-amber-600 dark:text-amber-400">{{ item.qty }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ item.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatPrice(item.price) }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ formatPrice(item.price * item.qty) }}</p>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div v-if="cart.items.length > 0">
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ formatPrice(cart.totalPrice) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Ongkos Kirim</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">Gratis</span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                        <div class="flex justify-between text-base font-bold">
                                            <span class="text-gray-900 dark:text-white">Total</span>
                                            <span class="text-amber-600 dark:text-amber-400">{{ formatPrice(cart.totalPrice) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <a
                                    :href="config.catalog_url ?? '/'"
                                    class="mt-6 flex items-center justify-center gap-2 w-full py-3 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Tambah Produk Lain
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
.expand-enter-active, .expand-leave-active { transition: all 0.3s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { opacity: 1; max-height: 400px; }
</style>
