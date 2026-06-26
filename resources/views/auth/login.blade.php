<x-layouts.public>
    <!-- Full-Screen Login Page with Hero Background -->
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden -mt-20 pt-20 py-12">
        
        <!-- Background Image with Overlay (Same as Landing Page) -->
        <div class="absolute inset-0 z-0 bg-gray-100 dark:bg-gray-900">
            @php
                $heroUrl = config('settings.hero_image_url') ? asset('storage/' . config('settings.hero_image_url')) : asset('assets/landing/hero.png');
            @endphp
            <img src="{{ $heroUrl }}" alt="Rima Craft Background" class="w-full h-full object-cover object-center opacity-60 dark:opacity-70 mix-blend-multiply dark:mix-blend-overlay" />
            <div class="absolute inset-0 bg-gradient-to-br from-white/98 via-white/90 to-white/70 dark:from-black/95 dark:via-black/90 dark:to-black/75"></div>
        </div>

        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode" class="absolute top-24 right-6 z-50 p-2.5 rounded-full bg-white/90 dark:bg-gray-900/90 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:scale-105 transition-all shadow-md">
            <span x-show="!darkMode">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </span>
            <span x-show="darkMode" style="display: none;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </span>
        </button>

        <!-- Main Content Grid -->
        <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-center">
                
                <!-- Left Side: Branding & Info (Like Landing Page) -->
                <div class="hidden lg:flex lg:col-span-3 flex-col space-y-6">
                    <!-- Logo -->
                    <a href="{{ route('catalog.index') }}" class="inline-block">
                        <span class="text-4xl font-serif font-bold text-gray-900 dark:text-white">Rima Craft</span>
                    </a>

                    <!-- Hero Text -->
                    <div class="space-y-4">
                        <h1 class="text-4xl xl:text-5xl font-serif font-bold text-gray-900 dark:text-white leading-tight">
                            Selamat Datang<br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-600 to-amber-400 dark:from-amber-300 dark:to-amber-500">Kembali</span>
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-300 font-light leading-relaxed">
                            Masuk untuk mengakses dashboard dan kelola bisnis kerajinan Anda dengan lebih efisien.
                        </p>
                    </div>

                    <!-- Features List -->
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-0.5">Manajemen Stok Real-time</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Pantau persediaan produk dan bahan baku secara langsung</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-0.5">Laporan Keuangan Otomatis</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tracking penjualan dan pembelian dengan laporan detail</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-0.5">Portal Customer & Partner</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Harga khusus reseller dan manajemen pesanan terintegrasi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Catalog Link -->
                    <div>
                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 font-medium transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Kembali ke Katalog
                        </a>
                    </div>
                </div>

                <!-- Right Side: Login Form Card -->
                <div class="lg:col-span-2 w-full max-w-md mx-auto">
                    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-xl border border-gray-200/50 dark:border-gray-800/50 p-6 sm:p-8">
                        
                        <!-- Mobile Logo -->
                        <div class="lg:hidden text-center mb-6">
                            <a href="{{ route('catalog.index') }}" class="inline-block">
                                <span class="text-3xl font-serif font-bold text-gray-900 dark:text-white">Rima Craft</span>
                            </a>
                        </div>

                        <!-- Form Header -->
                        <div class="text-center mb-6">
                            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-amber-500/30">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m0 0h14m-14 0h14m0 0v14m0-14V6"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">Masuk ke Akun</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Silakan masukkan kredensial Anda</p>
                        </div>

                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-5 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm border border-red-200 dark:border-red-800">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Login Form -->
                        <form hx-post="{{ route('login.store') }}" 
                              hx-target="body"
                              hx-swap="outerHTML"
                              class="space-y-4"
                              x-data="{ submitting: false }"
                              @submit="submitting = true"
                              @htmx:after-request="submitting = false">
                            @csrf
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                        class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none @error('email') border-red-500 @enderror"
                                        placeholder="nama@email.com">
                                </div>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                    <input type="password" id="password" name="password" required
                                        class="w-full pl-9 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none @error('password') border-red-500 @enderror"
                                        placeholder="••••••••">
                                </div>
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center">
                                <label class="flex items-center cursor-pointer group">
                                    <input type="checkbox" name="remember" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500 cursor-pointer">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-gray-200">Ingat saya</span>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                x-bind:disabled="submitting">
                                
                                <svg x-show="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="submitting ? 'Memproses...' : 'Masuk ke Dashboard'"></span>
                            </button>
                        </form>

                        <!-- Divider -->
                        <div class="mt-6 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-center text-xs text-gray-500 dark:text-gray-400 mb-3">Belum punya akun?</p>
                            
                            <!-- Registration Buttons -->
                            <div class="grid grid-cols-2 gap-2.5">
                                <a href="{{ route('register.show', 'customer') }}" 
                                   class="flex flex-col items-center justify-center px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg hover:border-gray-400 dark:hover:border-gray-500 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all group">
                                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 mb-1.5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white">Daftar Customer</span>
                                </a>
                                <a href="{{ route('register.show', 'partner') }}" 
                                   class="flex flex-col items-center justify-center px-3 py-2.5 border border-emerald-300 dark:border-emerald-600 rounded-lg hover:border-emerald-400 dark:hover:border-emerald-500 bg-emerald-50 dark:bg-emerald-900/10 hover:bg-emerald-100 dark:hover:bg-emerald-900/20 transition-all group">
                                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">Daftar Partner</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Trust Badge -->
                    <div class="mt-5 text-center">
                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Keamanan terjamin dengan enkripsi SSL
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
