<x-layouts.public>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 -mt-20 pt-20">
        
        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <a href="{{ route('catalog.index') }}" class="flex justify-center">
                <span class="text-3xl font-serif font-bold text-gray-900 dark:text-white">Rima Craft</span>
            </a>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                @if($type === 'partner')
                    Daftar sebagai Partner
                @else
                    Buat Akun Baru
                @endif
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                @if($type === 'partner')
                    Dapatkan harga reseller dan keuntungan khusus
                @else
                    Belanja kerajinan tradisional berkualitas
                @endif
            </p>
        </div>

        <!-- Form Card -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
            <div class="bg-white dark:bg-gray-900 py-8 px-4 shadow-xl sm:rounded-xl sm:px-10 border border-gray-200 dark:border-gray-800">
                
                @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800 dark:text-red-400">Terdapat {{ $errors->count() }} kesalahan:</h3>
                            <ul class="mt-2 text-sm text-red-700 dark:text-red-400 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                <form class="space-y-6" action="{{ route('register.submit', $type) }}" method="POST" 
                      x-data="{ loading: false }" 
                      @submit="loading = true">
                    @csrf

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat Email <span class="text-red-500">*</span></label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
                        <div class="mt-1">
                            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" placeholder="0812-xxxx-xxxx"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                        <div class="mt-1">
                            <textarea id="address" name="address" rows="3" 
                                      class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <!-- Partner-specific fields -->
                    @if($type === 'partner')
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informasi Bisnis</h3>
                        
                        <!-- Company Name -->
                        <div class="mb-4">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Perusahaan/Toko <span class="text-red-500">*</span></label>
                            <div class="mt-1">
                                <input id="company_name" name="company_name" type="text" required value="{{ old('company_name') }}" 
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            </div>
                        </div>

                        <!-- Business Type -->
                        <div>
                            <label for="business_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Usaha</label>
                            <div class="mt-1">
                                <input id="business_type" name="business_type" type="text" value="{{ old('business_type') }}" placeholder="Contoh: Retailer, Reseller, Distributor"
                                       class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Password -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Keamanan Akun</h3>
                        
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    <input id="password" name="password" type="password" required 
                                           class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Minimal 8 karakter</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Konfirmasi Password <span class="text-red-500">*</span></label>
                                <div class="mt-1">
                                    <input id="password_confirmation" name="password_confirmation" type="password" required 
                                           class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Agreement -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input id="agree_terms" name="agree_terms" type="checkbox" required 
                                   class="h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="agree_terms" class="font-medium text-gray-700 dark:text-gray-300">
                                Saya setuju dengan <a href="#" class="text-amber-600 hover:text-amber-500">Syarat dan Ketentuan</a> serta <a href="#" class="text-amber-600 hover:text-amber-500">Kebijakan Privasi</a> Rima Craft
                            </label>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" 
                                :disabled="loading"
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Memproses...' : 'Daftar Sekarang'"></span>
                        </button>
                    </div>
                </form>

                <!-- Login Link -->
                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">Sudah punya akun?</span>
                        </div>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:text-amber-500">
                            Login di sini
                        </a>
                    </div>
                </div>
            </div>

            <!-- Benefits Box -->
            @if($type === 'partner')
            <div class="mt-6 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/10 dark:to-teal-900/10 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6">
                <h3 class="text-sm font-semibold text-emerald-900 dark:text-emerald-400 uppercase tracking-wide mb-3">Keuntungan Partner</h3>
                <ul class="space-y-2 text-sm text-emerald-800 dark:text-emerald-300">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-emerald-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Harga khusus reseller (lebih murah 10-20%)
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-emerald-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Akses dashboard partner dengan analitik penjualan
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-emerald-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Riwayat transaksi dan invoice digital
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-emerald-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Prioritas untuk produk baru dan promosi eksklusif
                    </li>
                </ul>
            </div>
            @endif
        </div>
    </div>
</x-layouts.public>
