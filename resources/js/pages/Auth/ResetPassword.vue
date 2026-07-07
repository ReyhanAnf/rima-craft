<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    businessName: { type: String, default: 'Rima Craft' },
    token:        { type: String, required: true },
    email:        { type: String, default: '' },
});

const showPassword        = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    token:                 props.token,
    email:                 props.email,
    password:              '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Kata Sandi" />

    <main class="flex min-h-screen items-center justify-center bg-[#f7f2ea] px-5 py-16 dark:bg-[#12100d]">
        <div class="w-full max-w-md">

            <!-- Brand -->
            <div class="mb-8 text-center">
                <h1 class="font-serif text-3xl font-semibold text-stone-950 dark:text-white">
                    Buat password baru
                </h1>
                <p class="mt-3 text-sm leading-6 text-stone-500 dark:text-stone-400">
                    Masukkan password baru untuk akun <strong>{{ email }}</strong>.
                </p>
            </div>

            <!-- Form card -->
            <div class="rounded-[1.75rem] border border-white/70 bg-white/82 p-8 shadow-[0_24px_70px_rgba(71,48,28,0.12)] backdrop-blur-xl dark:border-white/10 dark:bg-stone-950/72">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Hidden fields -->
                    <input type="hidden" :value="form.token" name="token" />

                    <!-- Email (read-only) -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Email</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            readonly
                            class="w-full cursor-not-allowed rounded-2xl border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-500 outline-none dark:border-white/10 dark:bg-white/5 dark:text-stone-400"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- New password -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Password Baru</label>
                        <div class="relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                autofocus
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-2xl border border-stone-200 bg-white/90 px-4 py-3 pr-11 text-sm text-stone-900 placeholder-stone-400 outline-none transition focus:border-[#9f6b36] focus:ring-3 focus:ring-[#9f6b36]/18 dark:border-white/12 dark:bg-stone-900/80 dark:text-white dark:placeholder-stone-500"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200"
                                :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                            >
                                <i :class="showPassword ? 'pi pi-eye-slash' : 'pi pi-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm password -->
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-stone-800 dark:text-stone-100">Konfirmasi Password</label>
                        <div class="relative">
                            <input
                                v-model="form.password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                required
                                placeholder="Ulangi password baru"
                                class="w-full rounded-2xl border border-stone-200 bg-white/90 px-4 py-3 pr-11 text-sm text-stone-900 placeholder-stone-400 outline-none transition focus:border-[#9f6b36] focus:ring-3 focus:ring-[#9f6b36]/18 dark:border-white/12 dark:bg-stone-900/80 dark:text-white dark:placeholder-stone-500"
                            />
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600 dark:hover:text-stone-200"
                                :aria-label="showPasswordConfirm ? 'Sembunyikan password' : 'Tampilkan password'"
                            >
                                <i :class="showPasswordConfirm ? 'pi pi-eye-slash' : 'pi pi-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-[#9f6b36] px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_rgba(116,72,34,0.22)] transition hover:-translate-y-px hover:bg-[#744822] disabled:opacity-60"
                    >
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Menyimpan...
                        </span>
                        <span v-else>Simpan Password Baru</span>
                    </button>
                </form>
            </div>

            <p class="mt-8 text-center font-mono text-[10px] uppercase tracking-[0.18em] text-stone-400/70 dark:text-stone-600">
                {{ businessName }}
            </p>
        </div>
    </main>
</template>
