<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
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

const submit = () => {
    form.post(route('admin.login.store'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="flex items-center justify-center min-h-screen px-4 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        <!-- Theme Toggle -->
        <button @click="toggleTheme" class="fixed top-4 right-4 p-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all shadow-sm">
            <i :class="isDark ? 'pi pi-sun' : 'pi pi-moon'"></i>
        </button>

        <div class="w-full max-w-sm">
            <!-- Logo & Header -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl mb-3 shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Admin Panel</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ businessName }} Management</p>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm">
                <!-- Errors -->
                <div v-if="Object.keys(errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(error, key) in errors" :key="key" size="small" class="mb-1">
                        {{ error }}
                    </Message>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email -->
                    <div class="flex flex-col gap-1.5">
                        <label for="email" class="text-xs font-semibold text-gray-700 dark:text-gray-300">Email</label>
                        <InputText
                            id="email"
                            type="email"
                            v-model="form.email"
                            class="w-full"
                            placeholder="admin@rimacraft.com"
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
                        <label for="remember" class="text-xs text-gray-600 dark:text-gray-400 cursor-pointer">Ingat saya</label>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        label="Masuk"
                        :loading="form.processing"
                        class="w-full !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </form>

                <!-- Back to Site -->
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                    <Link :href="route('catalog.index')" class="flex items-center justify-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Kembali ke Website
                    </Link>
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-4 text-center text-xs text-gray-400 dark:text-gray-500">
                &copy; {{ new Date().getFullYear() }} {{ businessName }}
            </p>
        </div>
    </div>
</template>
