<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const props = defineProps({
    errors: Object,
    businessName: {
        type: String,
        default: 'Rima Craft'
    },
    type: {
        type: String, // 'customer' | 'partner' | 'admin'
        default: 'customer'
    }
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isDark = ref(localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches));

const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Initialize theme on mount
if (isDark.value) {
    document.documentElement.classList.add('dark');
} else {
    document.documentElement.classList.remove('dark');
}

const isAdmin = computed(() => props.type === 'admin');
const isReseller = computed(() => props.type === 'reseller');
const isCustomer = computed(() => props.type === 'customer');

const submit = () => {
    let postRoute;
    if (isAdmin.value) {
        postRoute = route('admin.login.store');
    } else if (isReseller.value) {
        postRoute = route('reseller.login.store');
    } else {
        postRoute = route('customer.login.store');
    }

    form.post(postRoute, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="isAdmin ? 'Admin Login' : (isReseller ? 'Reseller Login' : 'Masuk Akun Customer')" />

    <div class="flex min-h-screen bg-gray-50 dark:bg-[#080808] transition-colors duration-300 font-sans">
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="fixed top-4 right-4 z-50 p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all shadow-sm">
            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
        </button>

        <!-- Form Section -->
        <div :class="[
            'w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 md:p-16',
            isAdmin ? 'order-1' : 'order-2 bg-gradient-to-br from-transparent via-amber-500/5 to-transparent'
        ]">
            <div class="w-full max-w-sm">
                <!-- Logo & Header -->
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl mb-4 shadow-lg shadow-amber-500/20">
                        <svg v-if="isAdmin" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg v-else-if="isReseller" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <svg v-else class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ isAdmin ? 'Admin Panel' : (isReseller ? 'Portal Reseller' : 'Portal Pelanggan') }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ isAdmin ? `${businessName} Management Dashboard` : (isReseller ? `Masuk ke akun reseller & grosir ${businessName}` : `Masuk untuk berbelanja & melacak pesanan ${businessName}`) }}
                    </p>
                </div>

                <!-- Login Card -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm">
                    <!-- Errors -->
                    <div v-if="Object.keys(errors).length > 0" class="mb-4">
                        <Message severity="error" v-for="(error, key) in errors" :key="key" size="small" class="mb-1">
                            {{ error }}
                        </Message>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-xs font-semibold text-gray-700 dark:text-gray-300">Email</label>
                            <InputText
                                id="email"
                                type="email"
                                v-model="form.email"
                                class="w-full"
                                :placeholder="isAdmin ? 'admin@rimacraft.com' : (isReseller ? 'reseller@email.com' : 'customer@email.com')"
                                required
                                autofocus
                            />
                        </div>

                        <!-- Password -->
                        <div class="flex flex-col gap-1.5">
                            <label for="password" class="text-xs font-semibold text-gray-700 dark:text-gray-300">Password</label>
                            <Password
                                id="password"
                                v-model="form.password"
                                class="w-full"
                                :feedback="false"
                                toggleMask
                                placeholder="••••••••"
                                required
                                :inputStyle="{ width: '100%' }"
                            />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center gap-2">
                            <Checkbox id="remember" v-model="form.remember" binary />
                            <label for="remember" class="text-xs text-gray-650 dark:text-gray-400 cursor-pointer">Ingat saya</label>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            type="submit"
                            :label="isAdmin ? 'Masuk Admin' : 'Masuk'"
                            :loading="form.processing"
                            class="w-full !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold py-2.5 transition"
                        />
                    </form>

                    <!-- Bottom Links -->
                    <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-800 space-y-4">
                        <!-- Customer portal specific links -->
                        <div v-if="isCustomer" class="text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2.5">Belum memiliki akun?</p>
                            <div class="flex justify-center gap-4 text-xs font-bold mb-3">
                                <Link :href="route('customer.register')" class="text-amber-500 hover:underline">Daftar Akun Baru</Link>
                            </div>
                            <div class="text-[10px] text-gray-450 dark:text-gray-500">
                                Ingin menjadi mitra grosir? 
                                <a :href="route('reseller.login')" class="font-bold text-amber-500 hover:underline">Masuk Portal Reseller</a>
                            </div>
                        </div>

                        <!-- Reseller portal specific links -->
                        <div v-if="isReseller" class="text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-2.5">Belum memiliki akun reseller?</p>
                            <div class="flex justify-center gap-4 text-xs font-bold mb-3">
                                <Link :href="route('reseller.register')" class="text-amber-500 hover:underline">Daftar Reseller Baru</Link>
                            </div>
                            <div class="text-[10px] text-gray-455 dark:text-gray-500">
                                Pelanggan eceran? 
                                <a :href="route('customer.login')" class="font-bold text-amber-500 hover:underline">Masuk Portal Pelanggan</a>
                            </div>
                        </div>

                        <Link :href="route('catalog.index')" class="flex items-center justify-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Kembali ke Website
                        </Link>
                    </div>
                </div>

                <!-- Footer -->
                <p class="mt-6 text-center text-[10px] text-gray-400 dark:text-gray-500">
                    &copy; {{ new Date().getFullYear() }} {{ businessName }}
                </p>
            </div>
        </div>

        <!-- Image/Visual Section -->
        <div :class="[
            'hidden lg:block lg:w-1/2 relative bg-gray-950',
            isAdmin ? 'order-2 border-l border-gray-200 dark:border-gray-800' : 'order-1 border-r border-gray-200 dark:border-gray-800'
        ]">
            <img
                :src="isCustomer ? '/assets/customer-login-side.png' : '/assets/admin-login-side.png'"
                alt="Login Visual"
                class="absolute inset-0 w-full h-full object-cover opacity-75 dark:opacity-60 select-none"
            />
            <!-- Blur Glass Panel Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
            
            <!-- Graphic content inside image -->
            <div class="absolute bottom-16 left-16 right-16 z-10 text-white font-sans">
                <span class="inline-block text-[10px] uppercase font-bold tracking-widest text-amber-500 mb-2">
                    {{ isAdmin ? 'Sistem Manajemen Internal' : (isReseller ? 'Portal Reseller' : 'Pilihan Terbaik UMKM') }}
                </span>
                <h2 class="text-3xl font-serif font-bold mb-3 tracking-wide leading-tight">
                    {{ isAdmin ? 'Manajemen Efisien & Real-time' : (isReseller ? 'Kembangkan Bisnis Bersama Rima Craft' : 'Menyajikan Keindahan Seni Nusantara') }}
                </h2>
                <p class="text-gray-300 text-xs font-light leading-relaxed max-w-md">
                    {{ isAdmin ? 'Kelola persediaan, transaksi, produksi, dan analitik Rima Craft dalam satu dasbor terpadu.' : (isReseller ? 'Nikmati harga khusus grosir, penawaran eksklusif, pencatatan limit piutang, dan manajemen tagihan kemitraan.' : 'Bergabunglah bersama kami untuk memantau status pesanan, mencatat riwayat transaksi, dan menikmati kemudahan layanan terbaik kami.') }}
                </p>
            </div>
        </div>
    </div>
</template>
