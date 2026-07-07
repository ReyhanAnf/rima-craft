<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#030712">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Rima Craft">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/assets/icon.png">

    <title>{{ config('settings.seo_title') ?: config('settings.business_name', 'Rima Craft') . ' - Katalog Produk' }}</title>
    <meta name="description" content="{{ config('settings.seo_description', 'Katalog eksklusif kerajinan tangan dari Rima Craft.') }}">
    <meta name="keywords" content="{{ config('settings.seo_keywords', 'kerajinan, rima craft, anyaman, rotan, furniture') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Global App Config -->
    <script>
        window.rimacraft = {
            businessName: "{{ config('settings.business_name', 'Rima Craft') }}",
            businessPhone: "{{ config('settings.business_phone', '6281234567890') }}"
        };
    </script>

    <!-- Vite Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom Fonts */
        body {
            font-family: 'Outfit', sans-serif;
        }
        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: 'Lora', serif;
        }
        
        /* Hide scrollbar for cleaner look in the cart drawer */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-[#050505] text-gray-800 dark:text-gray-200 antialiased transition-colors duration-300">
    
    <!-- Navbar -->
    <header 
        x-data="{ scrolled: false }" 
        @scroll.window="scrolled = (window.pageYOffset > 20)"
        :class="scrolled ? 'bg-white/95 dark:bg-[#0a0a0a]/95 backdrop-blur-md border-b border-gray-200 dark:border-[#1a1a1a] shadow-lg' : 'bg-transparent border-b border-transparent'"
        class="fixed top-0 w-full z-40 transition-all duration-500"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between transition-all duration-500" :class="scrolled ? 'h-16' : 'h-24'">
            <div class="flex items-center">
                <h1 class="text-2xl md:text-3xl font-serif font-bold tracking-[0.15em] text-gray-900 dark:text-white uppercase transition-colors" :class="!scrolled && !darkMode ? 'text-gray-900 dark:text-white' : ''">{{ config('settings.business_name', 'Rima Craft') }}</h1>
            </div>
            
            <div class="flex items-center gap-2 md:gap-4">
                <button @click="darkMode = !darkMode" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition" :class="!scrolled && !darkMode ? 'text-gray-900' : ''">
                    <span x-show="!darkMode">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </span>
                    <span x-show="darkMode" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </span>
                </button>

                <button @click="$dispatch('open-cart')" class="relative flex items-center justify-center p-2 text-gray-900 dark:text-white hover:text-amber-500 transition-colors group" :class="!scrolled && !darkMode ? 'text-gray-900' : ''">
                    <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-show="$store.cart.totalItems() > 0" 
                          x-text="$store.cart.totalItems()" 
                          x-transition
                          class="absolute top-0 right-0 inline-flex items-center justify-center w-4 h-4 text-[9px] font-bold text-black bg-amber-500 rounded-full shadow-sm" style="display: none;">
                    </span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen pb-24">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-[#020202] border-t border-gray-200 dark:border-[#1a1a1a] pt-16 pb-8 transition-colors duration-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8 mb-16">
                <div class="md:col-span-2">
                    <h2 class="text-2xl font-serif font-bold tracking-widest text-gray-900 dark:text-white uppercase mb-6">{{ config('settings.business_name', 'Rima Craft') }}</h2>
                    <p class="text-gray-500 dark:text-gray-400 font-light leading-relaxed max-w-md">
                        {{ config('settings.hero_description', 'Menghadirkan mahakarya kerajinan Nusantara dengan sentuhan modern. Dibuat dengan penuh dedikasi oleh pengrajin lokal untuk mewujudkan estetika ruang impian Anda.') }}
                    </p>
                </div>
                
                <div>
                    <ul class="space-y-4 font-light text-sm text-gray-500 dark:text-gray-400">
                        <li><a href="#" @click.prevent="window.scrollTo({top: 0, behavior: 'smooth'})" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Beranda</a></li>
                        <li><a href="#katalog" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Koleksi Kami</a></li>
                        <li><a href="#kontak" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Lokasi & Kontak</a></li>
                    </ul>
                </div>
                
                <div>
                    <ul class="space-y-4 font-light text-sm text-gray-500 dark:text-gray-400">
                        <li><a href="{{ route('page.terms') }}" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Syarat & Ketentuan</a></li>
                        <li><a href="{{ route('page.privacy') }}" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Kebijakan Privasi</a></li>
                        <li><a href="{{ route('page.shipping') }}" class="hover:text-amber-600 dark:hover:text-amber-500 transition-colors">Pengiriman & Retur</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="pt-8 border-t border-gray-200 dark:border-[#1a1a1a] flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="text-xs text-gray-500 dark:text-gray-500 font-light tracking-wide">
                    &copy; {{ date('Y') }} {{ config('settings.business_name', 'Rima Craft') }}. Hak cipta dilindungi.
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ route('login') }}" class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600 hover:text-amber-600 dark:hover:text-amber-500 transition-colors duration-300">Login</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cart Drawer -->
    <div x-data="{ cartOpen: false }"
         @open-cart.window="cartOpen = true"
         class="relative z-50"
         x-show="cartOpen"
         style="display: none;">
        
        <div x-show="cartOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="cartOpen = false"></div>

        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div x-show="cartOpen"
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full"
                         class="pointer-events-auto w-screen max-w-md h-full bg-white dark:bg-gray-950 shadow-2xl flex flex-col border-l border-gray-200 dark:border-gray-800">
                        
                         <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-black/40">
                            <h2 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                Keranjang Belanja
                            </h2>
                            <div class="flex items-center gap-2">
                                <button x-show="$store.cart.items.length > 0" @click="$store.cart.clear()" class="text-[10px] font-bold text-red-500 hover:text-red-650 transition flex items-center gap-1 bg-red-500/5 hover:bg-red-500/10 px-2 py-1 rounded-md border border-red-500/20 cursor-pointer">
                                    Clean
                                </button>
                                <button @click="cartOpen = false" class="p-1.5 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4 hide-scrollbar bg-white dark:bg-[#050505]">
                            <template x-if="$store.cart.items.length === 0">
                                <div class="h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 space-y-4">
                                    <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="font-medium">Keranjang masih kosong</p>
                                    <button @click="cartOpen = false" class="px-4 py-2 bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400 rounded-lg font-semibold text-sm cursor-pointer">Mulai Belanja</button>
                                </div>
                            </template>

                            <template x-for="item in $store.cart.items" :key="item.id">
                                <div class="flex gap-4 p-3 bg-white dark:bg-black border border-gray-150 dark:border-gray-800/80 rounded-xl shadow-sm">
                                    <div class="w-24 h-24 rounded-lg bg-gray-100 dark:bg-gray-900 overflow-hidden flex-shrink-0 relative">
                                        <template x-if="item.image">
                                            <img :src="item.image" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.image">
                                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start">
                                                <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2" x-text="item.name"></h3>
                                                <button @click="$store.cart.remove(item.id)" class="text-gray-400 hover:text-red-500 transition ml-2 cursor-pointer">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
                                            <div class="text-primary-600 dark:text-primary-400 font-bold text-xs mt-1" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></div>
                                        </div>
                                        
                                        <div class="flex items-center justify-between mt-2">
                                            <div class="flex items-center border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
                                                <button @click="$store.cart.decrement(item.id)" class="px-2.5 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-850 transition font-bold cursor-pointer">-</button>
                                                <div class="px-2 py-1 text-xs font-semibold text-gray-900 dark:text-white min-w-[1.5rem] text-center" x-text="item.qty"></div>
                                                <button @click="$store.cart.increment(item.id)" class="px-2.5 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-850 transition font-bold cursor-pointer">+</button>
                                            </div>
                                            <div class="flex flex-col items-end">
                                                <div class="text-xs font-bold text-gray-900 dark:text-white" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></div>
                                                <template x-if="item.qty >= item.stock">
                                                    <span class="text-[8px] text-red-500 font-bold uppercase tracking-wide mt-0.5">Batas Stok</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Cart Footer -->
                        <div class="p-5 border-t border-gray-250 dark:border-gray-800 bg-gray-50 dark:bg-black/40" x-show="$store.cart.items.length > 0">
                            <!-- Catatan Pesanan -->
                            <div class="mb-4">
                                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Catatan Pesanan</label>
                                <textarea x-model="$store.cart.notes" @input="$store.cart.save()" rows="2" placeholder="Contoh: Request rotan warna gelap, kirim sebelum jam 3 sore, dll." 
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-gray-850 dark:text-gray-200 outline-none focus:ring-1 focus:ring-amber-500/50 resize-none transition-colors placeholder-gray-400 dark:placeholder-gray-600"></textarea>
                            </div>

                            <div class="flex justify-between items-center mb-4">
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Total Pesanan</span>
                                <span class="text-lg font-black text-gray-900 dark:text-white" x-text="'Rp ' + $store.cart.totalPrice().toLocaleString('id-ID')"></span>
                            </div>
                            
                            <!-- Checkout Method Selection -->
                            <div class="space-y-3" x-data="{ method: 'form' }">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih Metode Pemesanan</p>
                                
                                <!-- Method Options -->
                                <div class="grid grid-cols-2 gap-2">
                                    <button @click="method = 'form'" 
                                            :class="method === 'form' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'"
                                            class="p-3 rounded-xl border-2 transition-all text-left">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4" :class="method === 'form' ? 'text-amber-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-xs font-bold" :class="method === 'form' ? 'text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400'">Form Order</span>
                                        </div>
                                        <p class="text-[9px]" :class="method === 'form' ? 'text-amber-600 dark:text-amber-500' : 'text-gray-400'">Isi data & bayar langsung</p>
                                    </button>
                                    
                                    <button @click="method = 'whatsapp'" 
                                            :class="method === 'whatsapp' ? 'border-[#25D366] bg-green-50 dark:bg-green-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'"
                                            class="p-3 rounded-xl border-2 transition-all text-left">
                                        <div class="flex items-center gap-2 mb-1">
                                            <svg class="w-4 h-4" :class="method === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-400'" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                            </svg>
                                            <span class="text-xs font-bold" :class="method === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-600 dark:text-gray-400'">WhatsApp</span>
                                        </div>
                                        <p class="text-[9px]" :class="method === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-400'">Chat langsung via WA</p>
                                    </button>
                                </div>

                                <!-- Submit Button - Changes based on method -->
                                <template x-if="method === 'form'">
                                    <div>
                                        <a href="{{ route('order.checkout') }}" 
                                                class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-amber-600/20 transition duration-300 flex items-center justify-center gap-2 cursor-pointer hover:scale-[1.02] active:scale-[0.98]">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Pesan Sekarang (Isi Form)
                                        </a>
                                        <p class="text-[9px] text-center text-gray-400 dark:text-gray-650 mt-2 font-light">Isi data lengkap & pilih metode pembayaran</p>
                                    </div>
                                </template>

                                <template x-if="method === 'whatsapp'">
                                    <div>
                                        <button @click="$store.cart.checkout('{{ config('settings.business_phone', '6281234567890') }}')" 
                                                class="w-full py-3.5 px-4 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-[#25D366]/20 transition duration-300 flex items-center justify-center gap-2 cursor-pointer hover:scale-[1.02] active:scale-[0.98]">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                            Kirim Pesanan (WhatsApp)
                                        </button>
                                        <p class="text-[9px] text-center text-gray-400 dark:text-gray-650 mt-2 font-light">Pemesanan akan dilanjutkan via chat WhatsApp secara otomatis</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Toast Notification -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         @toast.window="show = true; message = $event.detail.message; type = $event.detail.type || 'success'; setTimeout(() => show = false, 4000)"
         class="fixed top-6 right-4 md:right-6 z-[100] flex flex-col gap-3 pointer-events-none"
         x-show="show"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-x-8"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-x-0"
         x-transition:leave-end="opacity-0 transform translate-x-8"
         style="display: none;">
        
        <!-- Success Toast -->
        <div x-show="type === 'success'" 
             class="pointer-events-auto max-w-sm bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-emerald-200/50 dark:border-emerald-500/30 backdrop-blur-xl overflow-hidden">
            <div class="flex items-start gap-3 p-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center shadow-lg shadow-emerald-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Berhasil!</p>
                    <p x-text="message" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"></p>
                </div>
                <button @click="show = false" class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="h-1 bg-gradient-to-r from-emerald-500 to-emerald-400 animate-pulse"></div>
        </div>

        <!-- Error Toast -->
        <div x-show="type === 'error'" 
             class="pointer-events-auto max-w-sm bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-red-200/50 dark:border-red-500/30 backdrop-blur-xl overflow-hidden">
            <div class="flex items-start gap-3 p-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center shadow-lg shadow-red-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Terjadi Kesalahan</p>
                    <p x-text="message" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"></p>
                </div>
                <button @click="show = false" class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="h-1 bg-gradient-to-r from-red-500 to-red-400 animate-pulse"></div>
        </div>

        <!-- Warning Toast -->
        <div x-show="type === 'warning'" 
             class="pointer-events-auto max-w-sm bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-amber-200/50 dark:border-amber-500/30 backdrop-blur-xl overflow-hidden">
            <div class="flex items-start gap-3 p-4">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center shadow-lg shadow-amber-500/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 dark:text-white mb-0.5">Peringatan</p>
                    <p x-text="message" class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed"></p>
                </div>
                <button @click="show = false" class="flex-shrink-0 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="h-1 bg-gradient-to-r from-amber-500 to-amber-400 animate-pulse"></div>
        </div>
    </div>

    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => console.log('ServiceWorker registered successfully'))
                    .catch(err => console.log('ServiceWorker registration failed: ', err));
            });
        }
    </script>
</body>
</html>

