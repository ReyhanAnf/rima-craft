<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    paymentMethods: { type: Array, default: () => [] },
    provinces:      { type: Array, default: () => [] },
    errors:         { type: Object, default: () => ({}) },
    isGuest:        { type: Boolean, default: true },
    isPartner:      { type: Boolean, default: false },
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
    customer_address: props.user?.address ?? '',
    province_id:      props.user?.province_id ?? '',
    city_id:          props.user?.city_id ?? '',
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
    payment_mode:         'full',
    down_payment_amount:  0,
});

// Region states
const cities = ref([]);
const loadingCities = ref(false);
const calculatingTotals = ref(false);

const subtotal = ref(cart.totalPrice);
const shippingCost = ref(0);
const finalTotal = ref(cart.totalPrice);
const updatedItems = ref([...cart.items]);

// DP state (partner only)
const paymentMode = ref('full')
const dpAmount    = ref(0)
const dpError     = ref('')

const remainingBalance = computed(() =>
    paymentMode.value === 'dp' ? Math.max(0, finalTotal.value - dpAmount.value) : 0
)

function validateDp() {
    // Reseller boleh DP 0% — seluruh tagihan dicatat sebagai piutang
    dpError.value = ''
}

const submitting = ref(false);
const showMobileSummary = ref(false);

const canSubmit = computed(() =>
    cart.items.length > 0 &&
    form.payment_method !== '' &&
    form.province_id !== '' &&
    form.city_id !== '' &&
    (paymentMode.value !== 'dp' || !dpError.value)
);

async function onProvinceChange() {
    form.city_id = '';
    cities.value = [];
    if (!form.province_id) return;
    
    loadingCities.value = true;
    try {
        const response = await fetch(`/api/regions/${form.province_id}/cities`);
        if (response.ok) {
            cities.value = await response.json();
        }
    } catch (e) {
        console.error('Failed to fetch cities', e);
    } finally {
        loadingCities.value = false;
    }
}

async function onCityChange() {
    if (!form.city_id) return;
    
    calculatingTotals.value = true;
    try {
        const response = await fetch('/api/regions/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                city_id: form.city_id,
                items: cart.items,
            }),
        });
        
        if (response.ok) {
            const data = await response.json();
            subtotal.value = data.subtotal;
            shippingCost.value = data.shipping_cost;
            finalTotal.value = data.total;
            updatedItems.value = data.items;
            validateDp();
        }
    } catch (e) {
        console.error('Failed to calculate totals', e);
    } finally {
        calculatingTotals.value = false;
    }
}

function submit() {
    if (!canSubmit.value) return;

    form.subtotal  = subtotal.value;
    form.shipping_cost = shippingCost.value;
    form.total     = finalTotal.value;
    form.items     = JSON.stringify(updatedItems.value);
    form.payment_mode        = paymentMode.value;
    form.down_payment_amount = paymentMode.value === 'dp' ? dpAmount.value : 0;

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

onMounted(async () => {
    if (form.province_id) {
        loadingCities.value = true;
        try {
            const response = await fetch(`/api/regions/${form.province_id}/cities`);
            if (response.ok) {
                cities.value = await response.json();
                if (form.city_id) {
                    await onCityChange();
                }
            }
        } catch (e) {
            console.error('Failed to load initial cities', e);
        } finally {
            loadingCities.value = false;
        }
    }
});
</script>

<template>
    <PublicLayout :config="config">
        <div class="min-h-screen bg-gray-50 dark:bg-gray-950 pt-28 md:pt-36 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Header -->
                <div class="text-center mb-8 md:mb-12">
                    <h1 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-2">Form Pemesanan</h1>
                    <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base max-w-xl mx-auto">Selesaikan pesanan Anda dengan mengisi detail pengiriman dan metode pembayaran di bawah ini.</p>
                </div>

                <!-- Auth Banner — shown to guests, sticky below navbar -->
                <div v-if="isGuest" class="sticky top-[70px] z-50 mb-6">
                    <div class="bg-amber-50 dark:bg-amber-950/80 border border-amber-300 dark:border-amber-700 rounded-2xl shadow-lg shadow-amber-500/10 backdrop-blur-sm overflow-hidden">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 md:p-5">
                            <!-- Icon + Text -->
                            <div class="flex items-start gap-3 flex-1">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 border border-amber-200 dark:border-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-amber-900 dark:text-amber-100">Login atau Daftar untuk Melanjutkan</p>
                                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">Form di bawah terkunci. Silakan login atau buat akun terlebih dahulu.</p>
                                </div>
                            </div>
                            <!-- CTA buttons -->
                            <div class="flex items-center gap-2 w-full sm:w-auto flex-shrink-0">
                                <a
                                    :href="(config.login_url ?? '/login') + '?redirect=' + encodeURIComponent(config.checkout_url ?? '/order/checkout')"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm transition-all shadow-sm"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                    Login
                                </a>
                                <a
                                    :href="'/register?redirect=' + encodeURIComponent(config.checkout_url ?? '/order/checkout')"
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 px-4 py-2.5 bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 hover:border-amber-500 text-amber-700 dark:text-amber-300 rounded-xl font-bold text-sm transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                    Daftar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Sticky/Collapsible Summary Drawer (visible on mobile only) -->
                <div class="lg:hidden mb-6 sticky top-[64px] z-40 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-md">
                    <button 
                        @click="showMobileSummary = !showMobileSummary" 
                        class="w-full flex items-center justify-between p-4 focus:outline-none"
                    >
                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="text-sm font-bold text-left">
                                {{ showMobileSummary ? 'Sembunyikan Ringkasan' : 'Tampilkan Ringkasan' }}
                                <span class="ml-1 px-2 py-0.5 rounded-full text-xs bg-amber-100 dark:bg-amber-900 text-amber-600 dark:text-amber-400">
                                    {{ cart.items.length }} produk
                                </span>
                            </span>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': showMobileSummary }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <span class="text-base font-black text-amber-600 dark:text-amber-400">{{ formatPrice(paymentMode === 'dp' ? dpAmount : finalTotal) }}</span>
                    </button>

                    <!-- Mobile Drawer Dropdown -->
                    <div v-show="showMobileSummary" class="border-t border-gray-150 dark:border-gray-800 p-4 bg-gray-50/50 dark:bg-gray-900/50 max-h-[60vh] overflow-y-auto">
                        <div class="space-y-3 mb-4">
                            <div v-for="item in updatedItems" :key="item.id" class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700/50 shadow-sm">
                                <div class="relative w-14 h-14 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                    <svg v-else class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-amber-500 text-white font-bold text-[10px] flex items-center justify-center shadow">
                                        {{ item.qty }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 break-words">{{ item.name }}</p>
                                    <p v-if="item.variantLabel" class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">Varian: {{ item.variantLabel }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        {{ item.qty }} x {{ formatPrice(item.price) }}
                                        <span v-if="item.has_discount" class="ml-1 text-[9px] bg-red-100 text-red-600 px-1 py-0.5 rounded font-semibold">Reseller</span>
                                    </p>
                                </div>
                                <p class="text-sm font-bold text-gray-900 dark:text-white flex-shrink-0">{{ formatPrice(item.subtotal || (item.price * item.qty)) }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-800 pt-3 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ formatPrice(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Ongkos Kirim</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ form.city_id ? (shippingCost > 0 ? formatPrice(shippingCost) : 'Gratis') : 'Pilih Kota/Kab' }}
                                </span>
                            </div>
                            <div v-if="paymentMode === 'dp'" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Pembayaran DP</span>
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ formatPrice(dpAmount) }}</span>
                            </div>
                            <div v-if="paymentMode === 'dp'" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Sisa Piutang</span>
                                <span class="font-semibold text-amber-600 dark:text-amber-400">{{ formatPrice(remainingBalance) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 dark:border-gray-800 pt-2 font-bold">
                                <span class="text-gray-900 dark:text-white">
                                    {{ paymentMode === 'dp' ? 'Total Harus Dibayar' : 'Total Akhir' }}
                                </span>
                                <span class="text-amber-600 dark:text-amber-400">
                                    {{ formatPrice(paymentMode === 'dp' ? dpAmount : finalTotal) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- Left: Order Form -->
                    <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                        <div
                            class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 md:p-10 shadow-sm relative overflow-hidden"
                            :class="{ 'select-none': isGuest }"
                        >
                            <!-- Locked overlay for guests -->
                            <div v-if="isGuest" class="absolute inset-0 z-10 bg-white/60 dark:bg-gray-900/70 backdrop-blur-[2px] rounded-3xl flex items-center justify-center">
                                <div class="flex flex-col items-center gap-3 text-center px-6">
                                    <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-950/50 border-2 border-amber-200 dark:border-amber-700 flex items-center justify-center shadow">
                                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-black text-gray-900 dark:text-white text-base">Form Terkunci</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Login atau daftar terlebih dahulu<br>untuk mengisi form pesanan.</p>
                                    </div>
                                    <div class="flex gap-2 mt-1">
                                        <a
                                            :href="(config.login_url ?? '/login') + '?redirect=' + encodeURIComponent(config.checkout_url ?? '/order/checkout')"
                                            class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-bold text-sm transition-all shadow-sm"
                                        >Login</a>
                                        <a
                                            :href="'/register?redirect=' + encodeURIComponent(config.checkout_url ?? '/order/checkout')"
                                            class="px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:border-amber-400 text-gray-700 dark:text-gray-200 rounded-xl font-bold text-sm transition-all"
                                        >Daftar</a>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-400 to-amber-600"></div>

                            <!-- Server-side errors -->
                            <div v-if="Object.keys(errors).length" class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm border border-red-200 dark:border-red-800/50">
                                <div class="flex gap-2">
                                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <div>
                                        <p class="font-bold mb-1">Terjadi kesalahan:</p>
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li v-for="(msg, field) in errors" :key="field">{{ msg }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="submit" class="space-y-8">

                                <!-- Step 1: Customer Info -->
                                <div class="space-y-6">
                                    <div class="flex items-center gap-3 border-b border-gray-150 dark:border-gray-800 pb-4">
                                        <div class="w-8 h-8 rounded-full bg-amber-500 text-gray-950 font-bold text-sm flex items-center justify-center shadow">1</div>
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Informasi Pengiriman</h3>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                                Nama Lengkap <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model="form.customer_name"
                                                    type="text" required
                                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                    placeholder="Masukkan nama lengkap"
                                                />
                                                <svg class="w-5 h-5 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            </div>
                                            <p v-if="form.errors.customer_name" class="mt-1.5 text-xs text-red-500">{{ form.errors.customer_name }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                                No. WhatsApp (Aktif) <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model="form.customer_phone"
                                                    type="tel" required
                                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                                    placeholder="Contoh: 081234567890"
                                                />
                                                <svg class="w-5 h-5 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            </div>
                                            <p v-if="form.errors.customer_phone" class="mt-1.5 text-xs text-red-500">{{ form.errors.customer_phone }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                v-model="form.customer_email"
                                                type="email" required
                                                :disabled="!isGuest"
                                                class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-gray-100 disabled:dark:bg-gray-900"
                                                placeholder="nama@email.com"
                                            />
                                            <svg class="w-5 h-5 text-gray-400 absolute right-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="mt-1.5 text-[10px] text-amber-600 dark:text-amber-400 font-semibold">
                                            Email terkunci karena Anda sedang login. Silakan logout jika ingin menggunakan email lain.
                                        </p>
                                        <p v-if="form.errors.customer_email" class="mt-1.5 text-xs text-red-500">{{ form.errors.customer_email }}</p>                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                                Provinsi <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                v-model="form.province_id"
                                                @change="onProvinceChange"
                                                required
                                                class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%2522%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M7%209l3%203%203-3%22%20stroke%3D%22%23a1a1aa%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_1rem_center] bg-no-repeat"
                                            >
                                                <option value="" disabled>Pilih Provinsi</option>
                                                <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                                    {{ prov.name }}
                                                </option>
                                            </select>
                                            <p v-if="form.errors.province_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.province_id }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                                Kota/Kabupaten <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <select
                                                    v-model="form.city_id"
                                                    @change="onCityChange"
                                                    :disabled="!form.province_id || loadingCities"
                                                    required
                                                    class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none cursor-pointer appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%2522%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M7%209l3%203%203-3%22%20stroke%3D%22%23a1a1aa%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-[length:1.25rem_1.25rem] bg-[right_1rem_center] bg-no-repeat disabled:bg-gray-100 disabled:dark:bg-gray-900"
                                                >
                                                    <option value="" disabled>{{ loadingCities ? 'Memuat...' : 'Pilih Kota/Kabupaten' }}</option>
                                                    <option v-for="city in cities" :key="city.id" :value="city.id">
                                                        {{ city.name }}
                                                    </option>
                                                </select>
                                                <span v-if="loadingCities" class="absolute right-10 top-1/2 -translate-y-1/2 flex h-4 w-4">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-4 w-4 bg-amber-500 animate-spin border border-white border-t-transparent"></span>
                                                </span>
                                            </div>
                                            <p v-if="form.errors.city_id" class="mt-1.5 text-xs text-red-500">{{ form.errors.city_id }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                            Alamat Lengkap Pengiriman <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            v-model="form.customer_address"
                                            required rows="3"
                                            class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                            placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota, provinsi, dan kodepos"
                                        />
                                        <p v-if="form.errors.customer_address" class="mt-1.5 text-xs text-red-500">{{ form.errors.customer_address }}</p>
                                    </div>
                                </div>

                                <!-- Step 2: Payment Methods -->
                                <div class="space-y-6">
                                    <div class="flex items-center gap-3 border-b border-gray-150 dark:border-gray-800 pb-4">
                                        <div class="w-8 h-8 rounded-full bg-amber-500 text-gray-950 font-bold text-sm flex items-center justify-center shadow">2</div>
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Metode Pembayaran</h3>
                                    </div>

                                    <div class="space-y-6">
                                        <div v-for="(methods, type) in groupedMethods" :key="type" class="space-y-3">
                                            <p class="text-[11px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                                {{ typeLabel[type] ?? type }}
                                            </p>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <label
                                                    v-for="method in methods"
                                                    :key="method.code"
                                                    class="relative flex items-center p-4 border-2 rounded-2xl cursor-pointer transition-all hover:border-amber-400 dark:hover:border-amber-600 bg-white dark:bg-gray-900 group"
                                                    :class="form.payment_method === method.code
                                                        ? 'border-amber-500 ring-2 ring-amber-500/10 bg-amber-50/20 dark:bg-amber-950/20 shadow-sm'
                                                        : 'border-gray-200 dark:border-gray-800'"
                                                >
                                                    <input
                                                        v-model="form.payment_method"
                                                        type="radio"
                                                        :value="method.code"
                                                        required
                                                        class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500 bg-transparent"
                                                    />
                                                    <div class="ml-3 flex-1 flex items-center justify-between gap-2">
                                                        <div class="min-w-0">
                                                            <!-- Display Name if no icon exists, or inside a clean layout -->
                                                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ method.name }}</p>
                                                            <p v-if="method.account_number" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium truncate">
                                                                {{ method.account_number }}
                                                            </p>
                                                        </div>

                                                        <!-- Show Payment Method Icon/Logo -->
                                                        <div class="flex-shrink-0 flex items-center justify-end h-8">
                                                            <img v-if="method.icon" :src="method.icon.startsWith('http') || method.icon.startsWith('/') ? method.icon : `/storage/${method.icon}`" :alt="method.name" class="h-8 max-w-[80px] object-contain" />
                                                            <template v-else>
                                                                <!-- Render nice styled default SVG logos based on codes -->
                                                                <div v-if="method.code === 'bca'" class="text-blue-700 font-extrabold italic text-sm tracking-wider font-mono">BCA</div>
                                                                <div v-else-if="method.code === 'mandiri'" class="text-blue-900 font-black italic text-xs tracking-tight">mandiri</div>
                                                                <div v-else-if="method.code === 'bri'" class="text-blue-600 font-extrabold text-sm tracking-tighter">BRI</div>
                                                                <div v-else-if="method.code === 'bni'" class="text-orange-500 font-extrabold italic text-sm">BNI</div>
                                                                <div v-else-if="method.code === 'gopay'" class="text-cyan-500 font-bold text-xs flex items-center gap-0.5">
                                                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 inline-block"></span>gopay
                                                                </div>
                                                                <div v-else-if="method.code === 'ovo'" class="text-purple-600 font-black text-xs">OVO</div>
                                                                <div v-else-if="method.code === 'dana'" class="text-blue-500 font-extrabold text-xs italic">DANA</div>
                                                                <div v-else-if="method.code === 'qris'" class="text-red-500 font-black text-xs tracking-widest border-2 border-red-500 px-1 py-0.5 rounded bg-red-50">QRIS</div>
                                                                <div v-else-if="method.code === 'cod'" class="text-emerald-600 font-bold text-[10px] uppercase bg-emerald-50 dark:bg-emerald-950/20 px-2 py-1 rounded border border-emerald-300 dark:border-emerald-800 flex items-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                                    COD
                                                                </div>
                                                                <svg v-else class="w-6 h-6 text-gray-300 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                                </svg>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <p v-if="form.errors.payment_method" class="text-xs text-red-500">{{ form.errors.payment_method }}</p>
                                </div>

                                <!-- Step 3: DP Option — partner only -->
                                <div v-if="isPartner" class="bg-blue-50/50 dark:bg-blue-950/10 border border-blue-200 dark:border-blue-800/40 rounded-2xl p-6 space-y-5">
                                    <div class="flex items-center gap-3 border-b border-blue-200/50 dark:border-blue-800/40 pb-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-500 text-white font-bold text-sm flex items-center justify-center shadow">3</div>
                                        <h3 class="text-lg font-black text-gray-900 dark:text-white">Opsi Pembayaran Partner</h3>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-xl border-2 flex-1 transition-all"
                                               :class="paymentMode === 'full' ? 'border-amber-500 bg-amber-50/20 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900'">
                                            <input type="radio" v-model="paymentMode" value="full" class="mt-0.5 w-4 h-4 text-amber-600 focus:ring-amber-500" />
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">Bayar Lunas</p>
                                                <p class="text-xs text-gray-500 mt-1">Bayar penuh seluruh pesanan sekarang</p>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 cursor-pointer p-4 rounded-xl border-2 flex-1 transition-all"
                                               :class="paymentMode === 'dp' ? 'border-amber-500 bg-amber-50/20 dark:bg-amber-950/20' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900'">
                                            <input type="radio" v-model="paymentMode" value="dp" class="mt-0.5 w-4 h-4 text-amber-600 focus:ring-amber-500" />
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 dark:text-white">Bayar DP (Uang Muka)</p>
                                                <p class="text-xs text-gray-500 mt-1">Minimum 0% di muka, sisanya dicatat sebagai piutang</p>
                                            </div>
                                        </label>
                                    </div>

                                    <Transition name="expand">
                                        <div v-if="paymentMode === 'dp'" class="space-y-4 pt-4 border-t border-blue-200/50 dark:border-blue-900/30">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                                                    Nominal Pembayaran DP (Min 0%) <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400 text-sm">Rp</span>
                                                    <input type="number" v-model.number="dpAmount" @input="validateDp" min="0"
                                                           class="w-full pl-10 pr-4 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all font-bold"
                                                           placeholder="Masukkan nominal DP" />
                                                </div>
                                                <p v-if="dpError" class="mt-2 text-xs text-red-500 flex items-center gap-1">
                                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    {{ dpError }}
                                                </p>
                                            </div>
                                            <div v-if="dpAmount > 0" class="flex justify-between items-center text-sm font-semibold p-4 bg-amber-50/50 dark:bg-amber-950/20 rounded-xl border border-amber-200/50 dark:border-amber-900/30">
                                                <span class="text-gray-700 dark:text-gray-300">Sisa Piutang Akhir:</span>
                                                <span class="text-amber-700 dark:text-amber-400 font-extrabold text-base">{{ formatPrice(remainingBalance) }}</span>
                                            </div>
                                        </div>
                                    </Transition>
                                </div>

                                <!-- Step 4: Notes -->
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3 border-b border-gray-150 dark:border-gray-800 pb-4">
                                        <div class="w-8 h-8 rounded-full bg-amber-500 text-gray-950 font-bold text-sm flex items-center justify-center shadow">
                                            {{ isPartner ? 4 : 3 }}
                                        </div>
                                        <h3 class="text-xl font-black text-gray-900 dark:text-white">Catatan Pesanan</h3>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">
                                            Catatan Tambahan (Opsional)
                                        </label>
                                        <textarea
                                            v-model="form.notes"
                                            rows="2.5"
                                            class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                            placeholder="Tulis instruksi khusus (contoh: request warna kemasan, titik koordinat, dll)..."
                                        />
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="pt-4">
                                    <button
                                        type="submit"
                                        :disabled="submitting || !canSubmit"
                                        class="w-full py-4 px-6 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-gray-950 rounded-2xl font-black text-base shadow-lg shadow-amber-500/20 hover:shadow-amber-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transform active:scale-[0.99]"
                                    >
                                        <span v-if="submitting" class="h-5 w-5 animate-spin rounded-full border-2 border-gray-950 border-t-transparent"></span>
                                        <span>{{ submitting ? 'Memproses Pesanan...' : 'Buat Pesanan Sekarang' }}</span>
                                    </button>
                                    <p class="text-[11px] text-center text-gray-500 dark:text-gray-400 mt-3">
                                        Dengan mengeklik tombol di atas, data pesanan Anda akan dicatat dan Anda akan diarahkan ke halaman detail transaksi & instruksi pembayaran.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Order Summary (Desktop only side panel) -->
                    <div class="hidden lg:block lg:col-span-5 xl:col-span-4 sticky top-24">
                        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm overflow-hidden relative">
                            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-amber-500 to-amber-600"></div>

                            <div class="flex items-center justify-between border-b border-gray-150 dark:border-gray-800 pb-4 mb-6">
                                <h3 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    Ringkasan Belanja
                                </h3>
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                    {{ cart.items.length }} produk
                                </span>
                            </div>

                            <div class="space-y-4 mb-6 max-h-[360px] overflow-y-auto pr-1">
                                <!-- Empty state -->
                                <div v-if="cart.items.length === 0" class="text-center py-10">
                                    <svg class="w-14 h-14 text-gray-300 dark:text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Keranjang belanja kosong</p>
                                    <a :href="config.catalog_url ?? '/'" class="inline-block mt-3 text-xs text-amber-600 hover:text-amber-700 font-bold">
                                        Mulai Belanja →
                                    </a>
                                </div>

                                <!-- Items -->
                                <div
                                    v-for="item in updatedItems"
                                    :key="item.id"
                                    class="flex items-center gap-4 p-3 bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100/50 dark:hover:bg-gray-800/80 rounded-2xl border border-gray-100 dark:border-gray-800 transition-all shadow-sm"
                                >
                                    <!-- Product Image with floating Qty Badge -->
                                    <div class="relative w-16 h-16 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 flex items-center justify-center overflow-hidden flex-shrink-0 shadow-inner">
                                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                                        <svg v-else class="w-7 h-7 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="absolute -top-1 -right-1 w-5.5 h-5.5 rounded-full bg-amber-500 text-white font-extrabold text-[10px] flex items-center justify-center shadow">
                                            {{ item.qty }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2 break-words" :title="item.name">{{ item.name }}</p>
                                        <p v-if="item.variantLabel" class="text-xs text-amber-600 dark:text-amber-400 font-semibold mt-0.5">Varian: {{ item.variantLabel }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ item.qty }} x {{ formatPrice(item.price) }}
                                            <span v-if="item.has_discount" class="ml-1 text-[9px] bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-400 px-1 py-0.5 rounded font-black">Reseller</span>
                                        </p>
                                    </div>
                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white flex-shrink-0">
                                        {{ formatPrice(item.subtotal || (item.price * item.qty)) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Totals and details -->
                            <div v-if="updatedItems.length > 0" class="border-t border-gray-150 dark:border-gray-800 pt-5 space-y-3.5">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Subtotal</span>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ formatPrice(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between text-sm items-center">
                                    <span class="text-gray-500 dark:text-gray-400 font-medium">Ongkos Kirim</span>
                                    <span class="font-bold text-gray-900 dark:text-white">
                                        {{ form.city_id ? (shippingCost > 0 ? formatPrice(shippingCost) : 'Gratis') : 'Pilih Kota/Kab' }}
                                    </span>
                                </div>
                                
                                <Transition name="expand">
                                    <div v-if="paymentMode === 'dp'" class="space-y-3.5 pt-3.5 border-t border-dashed border-gray-150 dark:border-gray-800">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">Pembayaran Uang Muka (DP)</span>
                                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatPrice(dpAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium">Sisa Piutang Pelunasan</span>
                                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ formatPrice(remainingBalance) }}</span>
                                        </div>
                                    </div>
                                </Transition>
                                
                                <div class="border-t border-gray-150 dark:border-gray-800 pt-4">
                                    <div class="flex justify-between text-base items-baseline">
                                        <span class="text-gray-900 dark:text-white font-black">
                                            {{ paymentMode === 'dp' ? 'Total Harus Dibayar Sekarang' : 'Total Akhir' }}
                                        </span>
                                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400 tracking-tight">
                                            {{ formatPrice(paymentMode === 'dp' ? dpAmount : finalTotal) }}
                                        </span>
                                    </div>
                                </div>

                                <a
                                    :href="config.catalog_url ?? '/'"
                                    class="mt-6 flex items-center justify-center gap-2 w-full py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-800 text-xs font-black text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 transition-all"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                    </svg>
                                    Kembali ke Galeri Produk
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
