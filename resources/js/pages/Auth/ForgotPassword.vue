<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    businessName: { type: String, default: 'Rima Craft' },
});

const page    = usePage();
const success = computed(() => page.props.flash?.success);

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Lupa Kata Sandi" />

    <main class="flex min-h-screen items-center justify-center bg-[#f7f2ea] px-5 py-16 dark:bg-[#12100d]">
        <div class="w-full max-w-md">

            <!-- Brand -->
            <div class="mb-8 text-center">
                <Link href="/" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 hover:text-stone-800 dark:text-stone-400 dark:hover:text-white">
                    <i class="pi pi-arrow-left text-[10px]"></i>
                    Kembali ke Login
                </Link>
                <h1 class="mt-6 font-serif text-3xl font-semibold text-stone-950 dark:text-white">
                    Lupa kata sandi?
                </h1>
                <p class="mt-3 text-sm leading-6 text-stone-500 dark:text-stone-400">
                    Masukkan email yang terdaftar. Kami akan mengirimkan link untuk membuat password baru.
                </p>
            </div>

            <!-- Success state -->
            <div v-if="success" class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/8 dark:text-emerald-300">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ success }}</span>
                </div>
            </div>

            <!-- Form card -->
            <div v-if="!success" class="rounded-[1.75rem] border border-white/70 bg-white/82 p-8 shadow-[0_24px_70px_rgba(71,48,28,0.12)] backdrop-blur-xl dark:border-white/10 dark:bg-stone-950/72">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-stone-800 dark:text-stone-100">
                            Alamat Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            placeholder="nama@email.com"
                            class="w-full rounded-2xl border border-stone-200 bg-white/90 px-4 py-3 text-sm text-stone-900 placeholder-stone-400 outline-none transition focus:border-[#9f6b36] focus:ring-3 focus:ring-[#9f6b36]/18 dark:border-white/12 dark:bg-stone-900/80 dark:text-white dark:placeholder-stone-500"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-2xl bg-[#9f6b36] px-5 py-3.5 text-sm font-bold text-white shadow-[0_12px_28px_rgba(116,72,34,0.22)] transition hover:-translate-y-px hover:bg-[#744822] hover:shadow-[0_16px_32px_rgba(116,72,34,0.28)] disabled:opacity-60"
                    >
                        <span v-if="form.processing" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Mengirim...
                        </span>
                        <span v-else>Kirim Link Reset</span>
                    </button>
                </form>
            </div>

            <!-- Back to login after success -->
            <div v-if="success" class="text-center">
                <Link
                    :href="route('login')"
                    class="inline-flex items-center gap-2 rounded-full bg-[#9f6b36] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#9f6b36]/25 transition hover:bg-[#744822]"
                >
                    Kembali ke Halaman Login
                </Link>
            </div>

            <p class="mt-8 text-center font-mono text-[10px] uppercase tracking-[0.18em] text-stone-400/70 dark:text-stone-600">
                {{ businessName }}
            </p>
        </div>
    </main>
</template>
