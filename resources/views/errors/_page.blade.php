<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status ?? 404 }} — Rima Craft</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/primeicons/primeicons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gray-950 text-white flex items-center justify-center p-4 relative overflow-hidden selection:bg-amber-500 selection:text-gray-950">
    @php
        $code = (int) ($status ?? 404);
        $details = match($code) {
            400 => [
                'title' => 'Permintaan Tidak Valid',
                'description' => 'Sistem tidak dapat memproses permintaan Anda karena format data tidak sesuai.',
                'icon' => 'pi-exclamation-circle',
                'badge' => 'Bad Request',
            ],
            401 => [
                'title' => 'Sesi Login Berakhir',
                'description' => 'Masa berlaku sesi Anda telah berakhir. Silakan login kembali untuk melanjutkan.',
                'icon' => 'pi-lock',
                'badge' => 'Unauthorized',
            ],
            403 => [
                'title' => 'Akses Ditolak',
                'description' => 'Anda tidak memiliki hak akses atau izin untuk membuka halaman ini.',
                'icon' => 'pi-shield',
                'badge' => 'Forbidden',
            ],
            404 => [
                'title' => 'Halaman Tidak Ditemukan',
                'description' => 'Halaman yang Anda cari tidak ada, telah dipindahkan, atau dihapus.',
                'icon' => 'pi-compass',
                'badge' => 'Page Not Found',
            ],
            503 => [
                'title' => 'Sistem Sedang Dipelihara',
                'description' => 'Website kami sedang dalam pemeliharaan berkala. Silakan coba beberapa saat lagi.',
                'icon' => 'pi-wrench',
                'badge' => 'Service Unavailable',
            ],
            default => [
                'title' => 'Terjadi Kesalahan Server',
                'description' => 'Maaf, terjadi kendala internal pada server kami. Tim teknis sedang memperbaikinya.',
                'icon' => 'pi-cog',
                'badge' => 'Internal Server Error',
            ],
        };
    @endphp

    <!-- Background Grid Decorative Pattern -->
    <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f293715_1px,transparent_1px),linear-gradient(to_bottom,#1f293715_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
    
    <!-- Glowing Gradient Orbs -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-lg w-full text-center space-y-6 bg-gray-900/60 backdrop-blur-xl border border-gray-800/80 p-8 sm:p-10 rounded-2xl shadow-2xl">
        <!-- Icon & Code Header -->
        <div class="inline-flex flex-col items-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center mb-4 shadow-inner">
                <i class="pi {{ $details['icon'] }} text-3xl text-amber-400 animate-pulse"></i>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-400 border border-amber-500/20 mb-2">
                {{ $details['badge'] }}
            </span>
            <h1 class="text-6xl sm:text-7xl font-extrabold tracking-tight bg-gradient-to-r from-amber-400 via-amber-500 to-amber-300 bg-clip-text text-transparent">
                {{ $code }}
            </h1>
        </div>

        <!-- Title & Description -->
        <div class="space-y-2">
            <h2 class="text-xl sm:text-2xl font-extrabold text-white">
                {{ $details['title'] }}
            </h2>
            <p class="text-xs sm:text-sm text-gray-400 leading-relaxed max-w-md mx-auto">
                {{ $details['description'] }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a
                href="/"
                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-gray-950 font-bold text-xs transition-all duration-200 shadow-lg shadow-amber-500/20 flex items-center justify-center gap-2"
            >
                <i class="pi pi-home text-xs"></i>
                Kembali ke Beranda
            </a>

            <button
                type="button"
                onclick="window.history.back()"
                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs border border-gray-700/60 transition-all duration-200 flex items-center justify-center gap-2"
            >
                <i class="pi pi-arrow-left text-xs"></i>
                Halaman Sebelumnya
            </button>
        </div>

        <!-- Footer brand info -->
        <div class="pt-6 border-t border-gray-800/60 text-[10px] text-gray-500 flex items-center justify-between">
            <span>Rima Craft System</span>
            <span>&copy; {{ date('Y') }} All rights reserved.</span>
        </div>
    </div>
</body>
</html>
