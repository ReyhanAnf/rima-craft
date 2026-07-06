<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';

const props = defineProps({
    type:         { type: String, default: 'customer' },
    businessName: { type: String, default: 'Rima Craft' },
    provinces:    { type: Array,  default: () => [] },
});

const registerType = ref(props.type === 'reseller' ? 'reseller' : 'customer');
const isReseller   = computed(() => registerType.value === 'reseller');

const form = useForm({
    register_type:         registerType.value,
    name:                  '',
    email:                 '',
    phone:                 '',
    address:               '',
    province_id:           null,
    city_id:               null,
    company_name:          '',
    business_type:         '',
    password:              '',
    password_confirmation: '',
    agree_terms:           false,
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

watch(registerType, (val) => {
    form.register_type = val;
    if (val === 'customer') {
        form.province_id   = null;
        form.city_id       = null;
        form.company_name  = '';
        form.business_type = '';
        cities.value       = [];
    }
});

const switchToReseller = () => { registerType.value = 'reseller'; };
const switchToCustomer = () => { registerType.value = 'customer'; };

const cities        = ref([]);
const loadingCities = ref(false);

watch(() => form.province_id, async (provinceId) => {
    form.city_id = null;
    cities.value = [];
    if (!provinceId) return;

    loadingCities.value = true;
    try {
        const res = await fetch(`/api/regions/${provinceId}/cities`);
        if (res.ok) cities.value = await res.json();
    } catch {
        cities.value = [];
    } finally {
        loadingCities.value = false;
    }
});

const pageTitle = computed(() => isReseller.value ? 'Daftar Reseller' : 'Daftar Akun Baru');
const headline = computed(() => isReseller.value ? 'Bangun jalur penjualan yang lebih tertata.' : 'Mulai belanja kerajinan dengan pengalaman yang lebih personal.');
const intro = computed(() => isReseller.value
    ? 'Ajukan akun reseller untuk akses harga khusus, pencatatan pesanan, dan proses kemitraan yang lebih rapi.'
    : 'Buat akun untuk menyimpan data pengiriman, memantau pesanan, dan menikmati katalog Rima Craft dengan lebih mudah.'
);

const submitForm = () => {
    form.post(route('register.submit'));
};
</script>

<template>
    <Head :title="pageTitle" />

    <main class="min-h-screen bg-[#f7f2ea] text-stone-950 transition-colors duration-300 dark:bg-[#12100d] dark:text-stone-50">
        <button
            type="button"
            @click="toggleTheme"
            class="fixed right-4 top-4 z-50 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/70 bg-white/85 text-stone-700 shadow-sm shadow-stone-900/10 backdrop-blur transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 focus:ring-offset-[#f7f2ea] dark:border-white/10 dark:bg-stone-950/70 dark:text-stone-200 dark:hover:bg-stone-900 dark:focus:ring-offset-[#12100d]"
            :aria-label="isDark ? 'Gunakan tema terang' : 'Gunakan tema gelap'"
        >
            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
        </button>

        <div class="grid min-h-screen lg:grid-cols-[0.92fr_1.08fr]">
            <section class="relative flex min-h-[300px] overflow-hidden lg:min-h-screen">
                <img
                    src="/assets/customer-login-side.png"
                    alt="Produk kerajinan Rima Craft"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="eager"
                />
                <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(36,24,14,0.12),rgba(36,24,14,0.78))] lg:bg-[linear-gradient(90deg,rgba(36,24,14,0.18),rgba(36,24,14,0.84))]"></div>

                <div class="relative z-10 flex w-full flex-col justify-between p-6 text-white sm:p-8 lg:p-12">
                    <Link href="/" class="inline-flex w-fit items-center gap-3 rounded-full border border-white/25 bg-white/15 px-4 py-2 backdrop-blur-md transition hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/70">
                        <span class="grid h-9 w-9 place-items-center rounded-full bg-[#f5d7a1] text-sm font-bold text-stone-950 shadow-sm">RC</span>
                        <span class="text-sm font-semibold tracking-wide">{{ businessName }}</span>
                    </Link>

                    <div class="max-w-xl pt-14 lg:pt-0">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.28em] text-[#f5d7a1]">
                            Pendaftaran Terpadu
                        </p>
                        <h1 class="max-w-lg font-serif text-4xl font-semibold leading-tight sm:text-5xl">
                            {{ headline }}
                        </h1>
                        <p class="mt-5 max-w-md text-sm leading-7 text-white/82 sm:text-base">
                            {{ intro }}
                        </p>

                        <div class="mt-8 grid max-w-lg grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-sm font-semibold">Customer</p>
                                <p class="mt-2 text-[11px] leading-4 text-white/75">Akun aktif langsung setelah pendaftaran.</p>
                            </div>
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-sm font-semibold">Reseller</p>
                                <p class="mt-2 text-[11px] leading-4 text-white/75">Form tambahan tersedia tanpa pindah halaman.</p>
                            </div>
                            <div class="rounded-2xl border border-white/18 bg-white/12 p-4 backdrop-blur-md">
                                <p class="text-sm font-semibold">Google Ready</p>
                                <p class="mt-2 text-[11px] leading-4 text-white/75">Satu gateway untuk semua akses publik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="flex items-center justify-center px-5 py-10 sm:px-8 lg:px-12">
                <div class="w-full max-w-[680px]">
                    <div class="mb-7">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#9f6b36] dark:text-[#f0c98e]">
                            {{ isReseller ? 'Kemitraan Reseller' : 'Akun Pelanggan' }}
                        </p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold text-stone-950 dark:text-white">
                            {{ isReseller ? 'Daftar sebagai reseller' : 'Buat akun baru' }}
                        </h2>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-stone-600 dark:text-stone-300">
                            {{ isReseller
                                ? 'Lengkapi data pribadi dan informasi usaha agar tim Rima Craft dapat meninjau pengajuan Anda.'
                                : 'Isi data dasar untuk mulai menyimpan alamat, riwayat transaksi, dan kebutuhan pesanan Anda.' }}
                        </p>
                    </div>

                    <div class="mb-5 rounded-[1.4rem] border border-white/70 bg-white/72 p-1.5 shadow-sm backdrop-blur-xl dark:border-white/10 dark:bg-stone-950/70">
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                type="button"
                                @click="switchToCustomer"
                                :class="[
                                    'rounded-2xl px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 dark:focus:ring-offset-[#12100d]',
                                    !isReseller
                                        ? 'bg-stone-950 text-white shadow-sm dark:bg-white dark:text-stone-950'
                                        : 'text-stone-500 hover:bg-white/70 hover:text-stone-950 dark:text-stone-400 dark:hover:bg-white/8 dark:hover:text-white'
                                ]"
                            >
                                <span class="block text-sm font-semibold">Pelanggan</span>
                                <span class="mt-1 block text-[11px] opacity-75">Belanja dan lacak pesanan</span>
                            </button>
                            <button
                                type="button"
                                @click="switchToReseller"
                                :class="[
                                    'rounded-2xl px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-[#9f6b36] focus:ring-offset-2 dark:focus:ring-offset-[#12100d]',
                                    isReseller
                                        ? 'bg-[#9f6b36] text-white shadow-sm'
                                        : 'text-stone-500 hover:bg-white/70 hover:text-stone-950 dark:text-stone-400 dark:hover:bg-white/8 dark:hover:text-white'
                                ]"
                            >
                                <span class="block text-sm font-semibold">Reseller</span>
                                <span class="mt-1 block text-[11px] opacity-75">Ajukan akun kemitraan</span>
                            </button>
                        </div>
                    </div>

                    <Transition name="fade-slide">
                        <div v-if="isReseller" class="mb-5 rounded-2xl border border-[#d8bd93] bg-[#fbf5ec] px-5 py-4 text-sm leading-6 text-[#744822] dark:border-[#f0c98e]/30 dark:bg-[#9f6b36]/12 dark:text-[#f0c98e]">
                            Pengajuan reseller akan aktif dalam status peninjauan. Setelah disetujui, akses harga dan fitur reseller akan terbuka penuh.
                        </div>
                    </Transition>

                    <div class="rounded-[1.75rem] border border-white/70 bg-white/82 p-6 shadow-[0_24px_70px_rgba(71,48,28,0.14)] backdrop-blur-xl dark:border-white/10 dark:bg-stone-950/72 dark:shadow-black/30 sm:p-8">
                        <div v-if="Object.keys(form.errors).length > 0" class="mb-5">
                            <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-2">
                                {{ err }}
                            </Message>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <input type="hidden" :value="registerType" name="register_type" />

                            <section class="space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-400">Data Akun</h3>
                                    <span class="text-[11px] text-stone-400">Wajib diisi</span>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label for="name" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Nama Lengkap</label>
                                        <InputText id="name" v-model="form.name" class="auth-input w-full" placeholder="Nama sesuai kontak" required />
                                        <span v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</span>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="email" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Alamat Email</label>
                                        <InputText id="email" v-model="form.email" type="email" class="auth-input w-full" placeholder="nama@email.com" required />
                                        <span v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label for="phone" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Nomor WhatsApp / Telepon</label>
                                        <InputText id="phone" v-model="form.phone" class="auth-input w-full" placeholder="0812..." />
                                    </div>

                                    <div class="space-y-2">
                                        <label for="address" class="text-sm font-semibold text-stone-800 dark:text-stone-100">Alamat Singkat</label>
                                        <Textarea id="address" v-model="form.address" class="auth-textarea w-full" rows="3" autoResize placeholder="Nama jalan, kota, atau area pengiriman" />
                                    </div>
                                </div>
                            </section>

                            <Transition name="fade-slide">
                                <section v-if="isReseller" class="space-y-4 rounded-2xl border border-stone-200 bg-[#fbf7f0] p-5 dark:border-white/10 dark:bg-white/5">
                                    <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-[#9f6b36] dark:text-[#f0c98e]">Informasi Bisnis</h3>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Provinsi</label>
                                            <Select
                                                v-model="form.province_id"
                                                :options="provinces"
                                                optionLabel="name"
                                                optionValue="id"
                                                placeholder="Pilih provinsi"
                                                class="auth-select w-full"
                                                filter
                                            />
                                            <span v-if="form.errors.province_id" class="text-xs text-red-500">{{ form.errors.province_id }}</span>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Kota / Kabupaten</label>
                                            <Select
                                                v-model="form.city_id"
                                                :options="cities"
                                                optionLabel="name"
                                                optionValue="id"
                                                placeholder="Pilih kota"
                                                class="auth-select w-full"
                                                filter
                                                :disabled="!form.province_id || loadingCities"
                                                :loading="loadingCities"
                                            />
                                            <span v-if="form.errors.city_id" class="text-xs text-red-500">{{ form.errors.city_id }}</span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Nama Perusahaan / Toko</label>
                                            <InputText v-model="form.company_name" class="auth-input w-full" placeholder="Nama toko atau brand" :required="isReseller" />
                                            <span v-if="form.errors.company_name" class="text-xs text-red-500">{{ form.errors.company_name }}</span>
                                        </div>

                                        <div class="space-y-2">
                                            <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Jenis Usaha</label>
                                            <InputText v-model="form.business_type" class="auth-input w-full" placeholder="Retailer, reseller, distributor" />
                                        </div>
                                    </div>
                                </section>
                            </Transition>

                            <section class="space-y-4 border-t border-stone-200 pt-5 dark:border-white/10">
                                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-stone-400">Keamanan Akun</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Password</label>
                                        <Password v-model="form.password" toggleMask :feedback="false" required class="auth-password w-full" inputClass="w-full" placeholder="Minimal 8 karakter" />
                                        <span class="text-[11px] text-stone-400">Gunakan minimal 8 karakter.</span>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Konfirmasi Password</label>
                                        <Password v-model="form.password_confirmation" toggleMask :feedback="false" required class="auth-password w-full" inputClass="w-full" placeholder="Ulangi password" />
                                    </div>
                                </div>
                            </section>

                            <div class="flex items-start gap-3 rounded-2xl bg-[#f4eadc] p-4 dark:bg-white/6">
                                <Checkbox id="agree_terms" v-model="form.agree_terms" binary required />
                                <label for="agree_terms" class="cursor-pointer text-xs leading-5 text-stone-600 dark:text-stone-300">
                                    Saya setuju dengan
                                    <Link :href="route('page.terms')" class="font-semibold text-[#9f6b36] hover:text-[#744822] dark:text-[#f0c98e]">Syarat & Ketentuan</Link>
                                    serta
                                    <Link :href="route('page.privacy')" class="font-semibold text-[#9f6b36] hover:text-[#744822] dark:text-[#f0c98e]">Kebijakan Privasi</Link>
                                    {{ businessName }}.
                                </label>
                            </div>

                            <Button
                                type="submit"
                                :label="isReseller ? 'Ajukan Akun Reseller' : 'Buat Akun Pelanggan'"
                                :loading="form.processing"
                                class="auth-primary-button w-full"
                            />
                        </form>

                        <div class="mt-7 border-t border-stone-200 pt-5 text-center dark:border-white/10">
                            <span class="text-sm text-stone-500 dark:text-stone-400">Sudah memiliki akun? </span>
                            <Link :href="route('login')" class="text-sm font-semibold text-[#9f6b36] hover:text-[#744822] dark:text-[#f0c98e]">
                                Masuk di sini
                            </Link>
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
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: opacity 0.22s ease, transform 0.22s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

:deep(.auth-input),
:deep(.auth-textarea),
:deep(.auth-password .p-password-input),
:deep(.auth-select) {
    border-radius: 1rem;
    border-color: rgb(231 229 228);
    background: rgba(255, 255, 255, 0.9);
    color: rgb(28 25 23);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

:deep(.auth-input),
:deep(.auth-password .p-password-input),
:deep(.auth-select) {
    min-height: 3rem;
}

:deep(.auth-input),
:deep(.auth-textarea),
:deep(.auth-password .p-password-input) {
    padding-left: 1rem;
    padding-right: 1rem;
}

:deep(.auth-textarea) {
    padding-top: 0.8rem;
    padding-bottom: 0.8rem;
}

:deep(.auth-input:enabled:focus),
:deep(.auth-textarea:enabled:focus),
:deep(.auth-password .p-password-input:enabled:focus),
:deep(.auth-select.p-focus) {
    border-color: #9f6b36;
    box-shadow: 0 0 0 3px rgba(159, 107, 54, 0.18);
}

:deep(.auth-password .p-password) {
    width: 100%;
}

:deep(.auth-primary-button) {
    min-height: 3.15rem;
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
:global(.dark) :deep(.auth-textarea),
:global(.dark) :deep(.auth-password .p-password-input),
:global(.dark) :deep(.auth-select) {
    border-color: rgba(255, 255, 255, 0.12);
    background: rgba(41, 37, 36, 0.82);
    color: white;
}
</style>
