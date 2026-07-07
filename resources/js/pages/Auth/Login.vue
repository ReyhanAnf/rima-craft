<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const props = defineProps({
    errors: { type: Object, default: () => ({}) },
    flash: { type: Object, default: () => ({}) },
    businessName: { type: String, default: 'Rima Craft' },
    type: { type: String, default: 'public' }, // 'public' | 'admin'
});

const page = usePage();
const siteConfig = computed(() => page.props.siteConfig || {});
const logoUrl = computed(() => siteConfig.value.logo_url ? `/storage/${siteConfig.value.logo_url}` : null);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const isDark = ref(
    localStorage.getItem('theme') === 'dark' ||
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
};

document.documentElement.classList.toggle('dark', isDark.value);

const isAdmin = computed(() => props.type === 'admin');

const visualImage = computed(() => isAdmin.value ? '/assets/admin-login-side.png' : '/assets/customer-login-side.png');
const eyebrow = computed(() => isAdmin.value ? 'Ruang Kerja Rima Craft' : 'Rima Craft Atelier');
const headline = computed(() => isAdmin.value ? 'Operasional rapi, keputusan lebih tenang.' : 'Kerajinan pilihan, pesanan tertata.');
const description = computed(() => isAdmin.value
    ? 'Pantau stok, produksi, penjualan, dan pelanggan dalam satu ruang kerja yang bersih.'
    : 'Masuk untuk melihat katalog, melacak pesanan, dan mengelola kebutuhan reseller dengan lebih mudah.'
);

const submit = () => {
    const postRoute = isAdmin.value ? route('admin.login.store') : route('login.store');
    form.post(postRoute, {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head :title="isAdmin ? 'Admin Login' : 'Masuk Akun'" />

    <main class="min-h-screen bg-[#f7f2ea] text-stone-950 transition-colors duration-300 dark:bg-[#12100d] dark:text-stone-50">
        <button
            type="button"
            @click="toggleTheme"
            class="fixed right-4 top-4 z-50 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-white/85 text-stone-700 shadow-sm shadow-stone-900/10 backdrop-blur transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 focus:ring-offset-[#f7f2ea] dark:border-white/10 dark:bg-stone-950/70 dark:text-stone-200 dark:hover:bg-stone-900 dark:focus:ring-offset-[#12100d]"
            :aria-label="isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
        >
            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
        </button>

        <div class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
            <section class="relative flex min-h-[320px] overflow-hidden lg:min-h-screen">
                <img
                    :src="visualImage"
                    :alt="isAdmin ? 'Ruang kerja operasional Rima Craft' : 'Produk kerajinan Rima Craft'"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                />
                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,24,14,0.18),rgba(36,24,14,0.74))] lg:bg-[linear-gradient(90deg,rgba(36,24,14,0.22),rgba(36,24,14,0.82))]"></div>
                <div class="relative z-10 flex w-full flex-col justify-between p-6 text-white sm:p-8 lg:p-12">
                    <Link href="/" class="inline-flex w-fit items-center gap-3 rounded-full border border-white/25 bg-white/15 px-4 py-2 backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="businessName"
                            class="h-9 w-9 rounded-full object-contain bg-white/90 p-1 shadow-sm"
                        />
                        <span v-else class="grid h-9 w-9 place-items-center rounded-full bg-[#f5d7a1] text-sm font-bold text-stone-950 shadow-sm">
                            RC
                        </span>
                        <span class="text-sm font-semibold tracking-wide">{{ businessName }}</span>
                    </Link>

                    <div class="max-w-xl pt-16 lg:pt-0">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.28em] text-[#f5d7a1]">{{ eyebrow }}</p>
                        <h1 class="max-w-lg font-serif text-4xl font-semibold leading-tight sm:text-5xl">
                            {{ headline }}
                        </h1>
                        <p class="mt-5 max-w-md text-sm leading-7 text-white/82 sm:text-base">
                            {{ description }}
                        </p>

                        <div class="mt-8 grid max-w-lg grid-cols-3 gap-3 text-left">
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-lg font-semibold">1</p>
                                <p class="mt-1 text-[11px] leading-4 text-white/75">Satu akses untuk pelanggan dan reseller</p>
                            </div>
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-lg font-semibold">24/7</p>
                                <p class="mt-1 text-[11px] leading-4 text-white/75">Katalog dan riwayat pesanan siap dicek</p>
                            </div>
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-lg font-semibold">ID</p>
                                <p class="mt-1 text-[11px] leading-4 text-white/75">Dibuat untuk ritme bisnis lokal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
                <div class="w-full max-w-[440px]">
                    <div class="mb-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#9f6b36] dark:text-[#f0c98e]">
                            {{ isAdmin ? 'Akses Tim Internal' : 'Selamat Datang Kembali' }}
                        </p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold text-stone-950 dark:text-white">
                            {{ isAdmin ? 'Masuk ke ruang kerja' : 'Masuk ke akun Anda' }}
                        </h2>
                        <p class="mt-3 text-sm leading-6 text-stone-600 dark:text-stone-300">
                            {{ isAdmin
                                ? 'Gunakan akun tim untuk melanjutkan pengelolaan Rima Craft.'
                                : 'Nikmati pengalaman belanja dan pengelolaan pesanan yang lebih personal.' }}
                        </p>
                    </div>

                    <div class="rounded-[1.75rem] border border-white/70 bg-white/82 p-6 shadow-[0_24px_70px_rgba(71,48,28,0.14)] backdrop-blur-xl dark:border-white/10 dark:bg-stone-950/72 dark:shadow-black/30 sm:p-8">
                        <div v-if="flash?.error || flash?.success || Object.keys(errors).length > 0" class="mb-5">
                            <Message v-if="flash?.error" severity="error" size="small" class="mb-2">
                                {{ flash.error }}
                            </Message>
                            <Message v-if="flash?.success" severity="success" size="small" class="mb-2">
                                {{ flash.success }}
                            </Message>
                            <Message severity="error" v-for="(error, key) in errors" :key="key" size="small" class="mb-2">
                                {{ error }}
                            </Message>
                        </div>

                        <form @submit.prevent="submit" class="space-y-5">
                            <div class="space-y-2">
                                <label for="email" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Email</label>
                                <InputText
                                    id="email"
                                    type="email"
                                    v-model="form.email"
                                    class="auth-input w-full"
                                    placeholder="nama@email.com"
                                    required
                                    autofocus
                                />
                            </div>

                            <div class="space-y-2">
                                <label for="password" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Password</label>
                                <Password
                                    id="password"
                                    v-model="form.password"
                                    class="auth-password w-full"
                                    :feedback="false"
                                    toggleMask
                                    placeholder="Masukkan password"
                                    required
                                    inputClass="w-full"
                                />
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <Checkbox id="remember" v-model="form.remember" binary />
                                    <label for="remember" class="cursor-pointer text-xs font-medium text-stone-600 dark:text-stone-300">Ingat saya</label>
                                </div>
                                <Link
                                    v-if="!isAdmin"
                                    :href="route('password.request')"
                                    class="text-xs font-semibold text-[#9f6b36] hover:text-[#744822] dark:text-[#f0c98e] transition-colors"
                                >
                                    Lupa kata sandi?
                                </Link>
                                <span v-else class="text-xs font-medium text-stone-400 dark:text-stone-500">Akses aman</span>
                            </div>

                            <Button
                                type="submit"
                                :label="isAdmin ? 'Masuk ke Dashboard' : 'Masuk Sekarang'"
                                :loading="form.processing"
                                class="auth-primary-button w-full"
                            />

                            <template v-if="!isAdmin">
                                <div class="flex items-center gap-3 py-1">
                                    <div class="h-px flex-1 bg-stone-200 dark:bg-white/10"></div>
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-stone-400">atau</span>
                                    <div class="h-px flex-1 bg-stone-200 dark:bg-white/10"></div>
                                </div>

                                <a
                                    :href="route('auth.google.redirect')"
                                    class="flex h-12 w-full items-center justify-center gap-3 rounded-2xl border border-stone-200 bg-white px-4 text-sm font-semibold text-stone-800 shadow-sm transition hover:-translate-y-0.5 hover:border-stone-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 dark:border-white/10 dark:bg-white/94 dark:text-stone-900"
                                >
                                    <svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                    Lanjutkan dengan Google
                                </a>
                            </template>
                        </form>

                        <div v-if="!isAdmin" class="mt-7 rounded-2xl bg-[#f4eadc] p-4 text-center dark:bg-white/6">
                            <p class="text-sm text-stone-600 dark:text-stone-300">Belum memiliki akun?</p>
                            <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <Link
                                    :href="route('register.show')"
                                    class="rounded-xl bg-stone-950 px-4 py-3 text-xs font-semibold text-white transition hover:bg-stone-800 focus:outline-none focus:ring-2 focus:ring-stone-950 focus:ring-offset-2 dark:bg-white dark:text-stone-950"
                                >
                                    Daftar Pelanggan
                                </Link>
                                <Link
                                    :href="route('register.show') + '?type=reseller'"
                                    class="rounded-xl border border-[#caa56f] px-4 py-3 text-xs font-semibold text-[#744822] transition hover:bg-[#ead2ad] focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 dark:border-[#f0c98e]/40 dark:text-[#f0c98e] dark:hover:bg-white/10"
                                >
                                    Daftar Reseller
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-between gap-4 text-xs text-stone-500 dark:text-stone-400">
                        <Link
                            :href="route('catalog.index')"
                            class="inline-flex items-center gap-2 font-semibold transition hover:text-[#9f6b36] dark:hover:text-[#f0c98e]"
                        >
                            <i class="pi pi-arrow-left text-[10px]"></i>
                            Kembali ke katalog
                        </Link>
                        <span>{{ businessName }}</span>
                    </div>
                </div>
            </section>
        </div>
    </main>
</template>

<style scoped>
:deep(.auth-input),
:deep(.auth-password .p-password-input) {
    min-height: 3rem;
    border-radius: 1rem;
    border-color: rgb(231 229 228);
    background: rgba(255, 255, 255, 0.9);
    padding-left: 1rem;
    padding-right: 1rem;
    color: rgb(28 25 23);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

:deep(.auth-input:enabled:focus),
:deep(.auth-password .p-password-input:enabled:focus) {
    border-color: #9f6b36;
    box-shadow: 0 0 0 3px rgba(159, 107, 54, 0.18);
}

:deep(.auth-password .p-password) {
    width: 100%;
}

:deep(.auth-primary-button) {
    min-height: 3.1rem;
    border-radius: 1rem;
    border-color: #9f6b36;
    background: #9f6b36;
    color: white;
    font-weight: 700;
    box-shadow: 0 16px 36px rgba(116, 72, 34, 0.2);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

:deep(.auth-primary-button:hover) {
    border-color: #744822;
    background: #744822;
    transform: translateY(-1px);
    box-shadow: 0 18px 42px rgba(116, 72, 34, 0.26);
}

:global(.dark) :deep(.auth-input),
:global(.dark) :deep(.auth-password .p-password-input) {
    border-color: rgba(255, 255, 255, 0.12);
    background: rgba(41, 37, 36, 0.82);
    color: white;
}
</style>
