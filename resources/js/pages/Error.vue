<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
    message: {
        type: String,
        default: null,
    },
});

const errorDetails = computed(() => {
    switch (props.status) {
        case 400:
            return {
                code: '400',
                title: 'Permintaan Tidak Valid',
                description: props.message || 'Sistem tidak dapat memproses permintaan Anda karena format data yang dikirimkan tidak sesuai.',
                icon: 'pi pi-exclamation-circle',
                badge: 'Bad Request',
                color: 'from-amber-500 to-amber-600',
            };
        case 401:
            return {
                code: '401',
                title: 'Sesi Login Berakhir',
                description: props.message || 'Anda belum masuk atau masa berlaku sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.',
                icon: 'pi pi-lock',
                badge: 'Unauthorized',
                color: 'from-amber-500 to-amber-600',
            };
        case 403:
            return {
                code: '403',
                title: 'Akses Ditolak',
                description: props.message || 'Anda tidak memiliki hak akses atau izin untuk membuka halaman ini.',
                icon: 'pi pi-shield',
                badge: 'Forbidden',
                color: 'from-red-500 to-amber-600',
            };
        case 404:
            return {
                code: '404',
                title: 'Halaman Tidak Ditemukan',
                description: props.message || 'Halaman yang Anda cari tidak ada, telah dipindahkan, atau telah dihapus dari sistem.',
                icon: 'pi pi-compass',
                badge: 'Page Not Found',
                color: 'from-amber-500 to-amber-600',
            };
        case 503:
            return {
                code: '503',
                title: 'Sistem Sedang Dipelihara',
                description: props.message || 'Website kami sedang dalam pemeliharaan berkala untuk peningkatan kualitas layanan. Silakan coba beberapa saat lagi.',
                icon: 'pi pi-wrench',
                badge: 'Service Unavailable',
                color: 'from-blue-500 to-amber-600',
            };
        case 500:
        default:
            return {
                code: String(props.status || '500'),
                title: 'Terjadi Kesalahan Server',
                description: props.message || 'Maaf, terjadi kendala internal pada server kami. Tim teknis telah menerima notifikasi dan sedang memperbaikinya.',
                icon: 'pi pi-cog',
                badge: 'Internal Server Error',
                color: 'from-red-500 to-amber-600',
            };
    }
});

function goBack() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
}
</script>

<template>
    <Head :title="`${errorDetails.code} — ${errorDetails.title}`" />

    <div class="min-h-screen bg-gray-950 text-white flex items-center justify-center p-4 relative overflow-hidden font-sans selection:bg-amber-500 selection:text-gray-950">
        <!-- Background Grid Decorative Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        
        <!-- Glowing Gradient Orbs -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-lg w-full text-center space-y-6 bg-gray-900/60 backdrop-blur-xl border border-gray-800/80 p-8 sm:p-10 rounded-2xl shadow-2xl">
            <!-- Icon & Code Header -->
            <div class="inline-flex flex-col items-center">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-4 shadow-inner">
                    <i :class="[errorDetails.icon, 'text-3xl text-amber-400 animate-pulse']"></i>
                </div>
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-2">
                    {{ errorDetails.badge }}
                </span>
                <h1 class="text-6xl sm:text-7xl font-extrabold tracking-tight bg-gradient-to-r from-amber-400 via-amber-500 to-amber-300 bg-clip-text text-transparent">
                    {{ errorDetails.code }}
                </h1>
            </div>

            <!-- Title & Description -->
            <div class="space-y-2">
                <h2 class="text-xl sm:text-2xl font-extrabold text-white">
                    {{ errorDetails.title }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-400 leading-relaxed max-w-md mx-auto">
                    {{ errorDetails.description }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <Link
                    href="/"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs transition-all duration-200 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2"
                >
                    <i class="pi pi-home text-xs"></i>
                    Kembali ke Beranda
                </Link>

                <button
                    type="button"
                    @click="goBack"
                    class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs border border-gray-700/60 transition-all duration-200 flex items-center justify-center gap-2"
                >
                    <i class="pi pi-arrow-left text-xs"></i>
                    Halaman Sebelumnya
                </button>
            </div>

            <!-- Footer brand info -->
            <div class="pt-6 border-t border-gray-800/60 text-[10px] text-gray-500 flex items-center justify-between">
                <span>Rima Craft System</span>
                <span>&copy; {{ new Date().getFullYear() }} All rights reserved.</span>
            </div>
        </div>
    </div>
</template>
