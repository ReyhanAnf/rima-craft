<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('settings.business_name', 'Rima Craft') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Trix Editor -->
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <style>
        trix-toolbar [data-trix-button-group="file-tools"] { display: none; }
        .trix-button { background-color: #f9fafb; }
        .dark .trix-button { background-color: #1f2937; color: #fff; filter: invert(1); }
        trix-editor { min-height: 200px; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-200 antialiased transition-colors duration-300" hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
    
    <div class="flex h-screen overflow-hidden">
        
        <!-- Desktop Sidebar (Hidden on Mobile) -->
        @auth
        <aside class="hidden md:flex flex-col w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-colors z-20 shadow-sm">
            <div class="p-4 flex items-center gap-3 border-b border-gray-100 dark:border-gray-800">
                <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center text-white font-bold">R</div>
                <h1 class="text-xl font-bold tracking-tight">{{ config('settings.business_name', 'Rima Craft') }}</h1>
            </div>
            
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('dashboard') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="{{ route('materials.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('materials.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Bahan Baku
                </a>
                <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('products.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Katalog Produk
                </a>
                <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('sales.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Penjualan
                </a>
                <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('orders.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Pesanan Online
                </a>
                <a href="{{ route('productions.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('productions.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    Produksi
                </a>
                <a href="{{ route('stock-adjustments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('stock-adjustments.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    Stok
                </a>
                <a href="{{ route('contacts.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('contacts.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Buku Kontak
                </a>
                <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('finance.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Buku Kas
                </a>
                <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-md {{ request()->routeIs('purchases.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }} font-medium transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Pembelian
                </a>
            </nav>

            <div class="p-4 border-t border-gray-100 dark:border-gray-800">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 mb-4 p-2 -mx-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group cursor-pointer" title="Kelola Profil & Password">
                    <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-sm font-bold group-hover:bg-primary-100 dark:group-hover:bg-primary-900 group-hover:text-primary-700 dark:group-hover:text-primary-400 transition-colors">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-bold truncate group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate uppercase tracking-widest mt-0.5">Edit Profil</p>
                    </div>
                </a>
                
                <div class="flex gap-2">
                    <button @click="darkMode = !darkMode" class="flex-1 flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-sm font-medium">
                        <span x-show="!darkMode" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                            Dark
                        </span>
                        <span x-show="darkMode" style="display: none;" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Light
                        </span>
                    </button>
                    
                    <form hx-post="{{ route('logout') }}" hx-target="body" hx-swap="outerHTML" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20 transition text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
                <!-- Settings Menu -->
                <div class="px-3 mb-2">
                    <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-3 mb-2 mt-4">Sistem & Pemasaran</div>
                    <a href="{{ route('galleries.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all font-medium text-sm {{ request()->routeIs('galleries.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('galleries.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Galeri
                    </a>
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-md transition-all font-medium text-sm {{ request()->routeIs('settings.*') ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                        <svg class="w-5 h-5 {{ request()->routeIs('settings.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Pengaturan Web
                    </a>
                </div>
            </div>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col relative overflow-hidden bg-gray-50 dark:bg-gray-950">
            
            <!-- Mobile Header -->
            @auth
            <header class="md:hidden glass px-4 py-2.5 sticky top-0 z-20 flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded bg-primary-500 flex items-center justify-center text-white font-bold text-xs">R</div>
                    <h1 class="text-lg font-bold">{{ config('settings.business_name', 'Rima Craft') }}</h1>
                </div>
                <button @click="darkMode = !darkMode" class="p-2 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                    <span x-show="!darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </span>
                    <span x-show="darkMode" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </span>
                </button>
            </header>
            @endauth

            <!-- Main Content Scrollable Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-4 pb-24 md:pb-8">
                <div class="max-w-6xl mx-auto">
                    {{ $slot }}
                </div>
            </main>

            <!-- Mobile Bottom Nav -->
            @auth
            <nav class="md:hidden glass px-4 py-3 flex justify-between absolute bottom-0 w-full z-20 border-t border-gray-200 dark:border-gray-800 pb-[calc(env(safe-area-inset-bottom)+0.75rem)]">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('dashboard') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span class="text-[10px] font-semibold">Home</span>
                </a>
                
                <a href="{{ route('sales.index') }}" class="flex flex-col items-center {{ request()->routeIs('sales.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="text-[10px] font-medium">Jual</span>
                </a>
                
                <a href="{{ route('purchases.index') }}" class="flex flex-col items-center {{ request()->routeIs('purchases.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="text-[10px] font-medium">Beli</span>
                </a>

                <a href="{{ route('finance.index') }}" class="flex flex-col items-center {{ request()->routeIs('finance.*') ? 'text-primary-600 dark:text-primary-400' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300' }}">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-[10px] font-medium">Kas</span>
                </a>

                <button type="button" @click="$dispatch('open-mobile-menu')" class="flex flex-col items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <span class="text-[10px] font-medium">Menu</span>
                </button>
            </nav>
            @endauth

        </div>
    </div>

    <!-- Mobile Menu Modal -->
    @auth
    <div x-data="{ mobileMenuOpen: false }"
         @open-mobile-menu.window="mobileMenuOpen = true"
         class="relative z-[80] md:hidden"
         x-show="mobileMenuOpen"
         style="display: none;">
        <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>
        <div class="fixed inset-x-0 bottom-0 z-10">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="bg-white dark:bg-gray-900 rounded-t-2xl shadow-2xl border-t border-gray-200 dark:border-gray-800 p-4 pb-[calc(env(safe-area-inset-bottom)+1.5rem)]">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Menu Lainnya</h3>
                    <button @click="mobileMenuOpen = false" class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <a href="{{ route('materials.index') }}" class="flex flex-col items-center justify-center gap-2 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span class="text-[10px] font-bold">Bahan</span>
                    </a>
                    <a href="{{ route('products.index') }}" class="flex flex-col items-center justify-center gap-2 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-[10px] font-bold">Produk</span>
                    </a>
                    <a href="{{ route('orders.index') }}" class="flex flex-col items-center justify-center gap-2 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span class="text-[10px] font-bold">Pesanan</span>
                    </a>
                    <a href="{{ route('productions.index') }}" class="flex flex-col items-center justify-center gap-2 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        <span class="text-[10px] font-bold">Produksi</span>
                    </a>
                    <a href="{{ route('stock-adjustments.index') }}" class="flex flex-col items-center justify-center gap-2 p-3 rounded-md bg-gray-50 dark:bg-gray-800/50 text-gray-700 dark:text-gray-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span class="text-[10px] font-bold">Stok</span>
                    </a>
                </div>

                <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <form hx-post="{{ route('logout') }}" hx-target="body" hx-swap="outerHTML">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-3 rounded-md bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Keluar / Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Global Slide-over Drawer -->
    <div x-data="{ drawerOpen: false }"
         @open-drawer.window="drawerOpen = true"
         @close-drawer.window="drawerOpen = false"
         class="relative z-50"
         x-show="drawerOpen"
         style="display: none;"
         aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        
        <div x-show="drawerOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="drawerOpen = false"></div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 md:pl-0">
                    <div x-show="drawerOpen"
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-lg h-full bg-white dark:bg-gray-950 shadow-2xl border-l border-gray-200 dark:border-gray-800 flex flex-col">
                        
                        <!-- HTMX Target -->
                        <div id="drawer-content" class="flex-1 overflow-y-auto flex flex-col relative">
                            <!-- Injected content should provide its own padding -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast Notification -->
    <div x-data="{ 
            show: {{ session()->has('toast') ? 'true' : 'false' }}, 
            message: '{{ session()->has('toast') ? addslashes(session('toast')['message']) : '' }}', 
            type: '{{ session()->has('toast') ? session('toast')['type'] : 'success' }}' 
         }"
         @toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 3000)"
         x-init="if(show) setTimeout(() => show = false, 3000)"
         class="fixed bottom-20 md:bottom-auto md:top-6 right-4 md:right-6 z-[60] flex flex-col gap-2 pointer-events-none"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4 md:translate-y-0 md:translate-x-4"
         x-transition:enter-end="opacity-100 transform translate-y-0 md:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2 md:-translate-y-2"
         style="display: none;">
        <div class="pointer-events-auto px-3.5 py-2.5 rounded-md shadow-xl border text-xs font-semibold flex items-center gap-2.5 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md"
             :class="type === 'success' ? 'border-emerald-200 text-emerald-800 dark:border-emerald-500/20 dark:text-emerald-400' : 'border-red-200 text-red-800 dark:border-red-500/20 dark:text-red-400'">
            <svg x-show="type === 'success'" class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            <svg x-show="type === 'error'" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span x-text="message" class="tracking-wide"></span>
        </div>
    </div>

    @auth
    @php $globalAccounts = \App\Models\Account::all(); @endphp
    <!-- Global Payment Modal -->
    <div x-data="{
        showPaymentModal: false,
        payableType: '',
        payableId: '',
        total: 0,
        paid: 0,
        amount: 0
    }"
    @open-payment-modal.window="
        showPaymentModal = true;
        payableType = $event.detail.type;
        payableId = $event.detail.id;
        total = $event.detail.total;
        paid = $event.detail.paid;
        amount = total - paid;
    ">
        <div x-show="showPaymentModal" class="relative z-[70]" style="display: none;">
            <div x-show="showPaymentModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showPaymentModal" 
                         @click.outside="showPaymentModal = false"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-md bg-white dark:bg-gray-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 dark:border-gray-800">
                        <form action="{{ route('payments.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="payable_type" x-model="payableType">
                            <input type="hidden" name="payable_id" x-model="payableId">
                            
                            <div class="px-4 pb-4 pt-5 sm:p-4 sm:pb-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Bayar Cicilan / Tagihan</h3>
                                
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3 mb-4 flex justify-between items-center border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <div class="text-[10px] text-gray-500 uppercase font-semibold">Sisa Tagihan</div>
                                        <div class="text-lg font-bold text-red-600 dark:text-red-400" x-text="'Rp ' + (total - paid).toLocaleString('id-ID')"></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-gray-500 uppercase font-semibold">Grand Total</div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white" x-text="'Rp ' + total.toLocaleString('id-ID')"></div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal Bayar</label>
                                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <input type="hidden" name="account_id" value="1">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nominal Dibayar (Rp)</label>
                                        <input type="number" name="amount" x-model="amount" min="1" :max="total - paid" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500 font-bold text-primary-700 dark:text-primary-400">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-4">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Simpan Pembayaran</button>
                                <button type="button" @click="showPaymentModal = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endauth

</body>
</html>

