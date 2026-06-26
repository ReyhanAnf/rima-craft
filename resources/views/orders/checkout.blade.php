<x-layouts.public>
    <!-- Order Form Page -->
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-2">Form Pemesanan</h1>
                <p class="text-gray-600 dark:text-gray-400">Lengkapi data di bawah untuk memproses pesanan Anda</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left: Order Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 md:p-8 shadow-sm">
                        
                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm border border-red-200 dark:border-red-800/50">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Order Form -->
                        <form action="{{ route('order.store') }}" method="POST"
                              class="space-y-6"
                              x-data="{ 
                                  paymentMethod: '',
                                  submitting: false,
                                  createAccount: false,
                                  password: '',
                                  passwordConfirmation: '',
                                  customerName: '{{ auth()->check() ? auth()->user()->name : '' }}',
                                  customerEmail: '{{ auth()->check() ? auth()->user()->email : '' }}',
                                  customerPhone: '{{ auth()->check() ? (auth()->user()->phone ?? '') : '' }}',
                                  customerAddress: '',
                                  cartItems: [],
                                  init() {
                                      // Sync with cart store
                                      this.cartItems = this.$store.cart.items;
                                      // Watch for changes
                                      this.$watch('$store.cart.items', value => {
                                          this.cartItems = value;
                                      });
                                  },
                                  prepareItems() {
                                      const itemsInput = this.$el.querySelector('input[name="items"]');
                                      if (itemsInput) {
                                          itemsInput.value = JSON.stringify(this.cartItems);
                                      }
                                  },
                                  get canSubmit() {
                                      return this.cartItems.length && this.paymentMethod !== '';
                                  }
                              }"
                              @submit="prepareItems(); submitting = true">
                            @csrf
                            
                            <!-- Customer Info -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Informasi Pelanggan</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Nama Lengkap <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="customer_name" x-model="customerName" required
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                               placeholder="Masukkan nama lengkap">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            No. WhatsApp <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel" name="customer_phone" x-model="customerPhone" required
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                               placeholder="081234567890">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" name="customer_email" x-model="customerEmail" required
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                           placeholder="nama@email.com">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email akan digunakan untuk login jika Anda membuat akun</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Alamat Pengiriman <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="customer_address" x-model="customerAddress" required rows="3"
                                              class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                              placeholder="Masukkan alamat lengkap untuk pengiriman"></textarea>
                                </div>
                            </div>

                            <!-- Create Account Option -->
                            @guest
                            <div class="bg-gradient-to-r from-amber-50 to-amber-100/50 dark:from-amber-900/20 dark:to-amber-800/10 border border-amber-200 dark:border-amber-800/50 rounded-xl p-5">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="create_account" value="1" x-model="createAccount" 
                                           class="mt-1 w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">Buat Akun Sekarang</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Dapatkan akses untuk melacak pesanan dan melihat riwayat pembelian Anda</p>
                                    </div>
                                </label>

                                <!-- Password Fields (shown when createAccount is checked) -->
                                <div x-show="createAccount" x-transition class="mt-4 space-y-4 pt-4 border-t border-amber-200 dark:border-amber-800/50">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Password <span class="text-red-500">*</span>
                                        </label>
                                        <input type="password" name="password" x-model="password" 
                                               :required="createAccount"
                                               minlength="8"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                               placeholder="Minimal 8 karakter">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Konfirmasi Password <span class="text-red-500">*</span>
                                        </label>
                                        <input type="password" name="password_confirmation" x-model="passwordConfirmation" 
                                               :required="createAccount"
                                               minlength="8"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
                                               placeholder="Ulangi password">
                                    </div>
                                </div>
                            </div>
                            @endguest

                            <!-- Payment Method -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Metode Pembayaran</h3>
                                
                                <div class="grid grid-cols-1 gap-4">
                                    @php
                                        $paymentMethods = \App\Models\PaymentMethod::active()->ordered()->get();
                                        $groupedMethods = $paymentMethods->groupBy('type');
                                    @endphp

                                    @foreach($groupedMethods as $type => $methods)
                                        <div>
                                            <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                                                @if($type === 'bank') Transfer Bank
                                                @elseif($type === 'ewallet') E-Wallet
                                                @elseif($type === 'qris') QRIS
                                                @elseif($type === 'cod') Bayar di Tempat
                                                @else {{ ucfirst($type) }}
                                                @endif
                                            </p>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                @foreach($methods as $method)
                                                    <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-amber-300 dark:hover:border-amber-600"
                                                           :class="paymentMethod === '{{ $method->code }}' ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20' : 'border-gray-200 dark:border-gray-700'">
                                                        <input type="radio" name="payment_method" value="{{ $method->code }}" 
                                                               x-model="paymentMethod" required
                                                               class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                                                        <div class="ml-3 flex-1">
                                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $method->name }}</p>
                                                            @if($method->account_number)
                                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $method->account_number }} - {{ $method->account_name }}</p>
                                                            @endif
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                    Catatan Pesanan (Opsional)
                                </label>
                                <textarea name="notes" x-model="$store.cart.notes" rows="2"
                                          class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none resize-none"
                                          placeholder="Request khusus, instruksi pengiriman, dll"></textarea>
                            </div>

                            <!-- Hidden Fields -->
                            <input type="hidden" name="order_method" value="form">
                            <input type="hidden" name="subtotal" :value="$store.cart.totalPrice()">
                            <input type="hidden" name="shipping_cost" value="0">
                            <input type="hidden" name="total" :value="$store.cart.totalPrice()">
                            <input type="hidden" name="items" value="">

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full py-4 px-6 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-gray-950 rounded-xl font-bold text-sm shadow-lg shadow-amber-500/20 hover:shadow-amber-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="submitting || !canSubmit">
                                <span x-text="submitting ? 'Memproses...' : 'Buat Pesanan'"></span>
                            </button>

                            <p class="text-xs text-center text-gray-500 dark:text-gray-400">
                                Setelah membuat pesanan, Anda akan diarahkan ke halaman konfirmasi dengan instruksi pembayaran
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 shadow-sm sticky top-24">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ringkasan Pesanan</h3>
                        
                        <!-- Cart Items -->
                        <div class="space-y-3 mb-6">
                            <template x-if="cartItems.length === 0">
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Keranjang masih kosong</p>
                                    <a href="{{ route('catalog.index') }}" class="inline-block mt-3 text-sm text-amber-600 hover:text-amber-700 font-medium">
                                        Mulai Belanja →
                                    </a>
                                </div>
                            </template>
                            
                            <template x-for="item in cartItems" :key="item.id">
                                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <div class="flex-shrink-0 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                        <span class="text-sm font-bold text-amber-600 dark:text-amber-400" x-text="item.qty"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="item.name"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400" x-text="'Rp ' + item.price.toLocaleString('id-ID')"></p>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="'Rp ' + (item.price * item.qty).toLocaleString('id-ID')"></p>
                                </div>
                            </template>
                        </div>

                        <!-- Total -->
                        <template x-if="cartItems.length > 0">
                            <div>
                                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                        <span class="font-semibold text-gray-900 dark:text-white" x-text="'Rp ' + cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0).toLocaleString('id-ID')"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Ongkos Kirim</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">Gratis</span>
                                    </div>
                                    <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                                        <div class="flex justify-between text-base font-bold">
                                            <span class="text-gray-900 dark:text-white">Total</span>
                                            <span class="text-amber-600 dark:text-amber-400" x-text="'Rp ' + cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0).toLocaleString('id-ID')"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Continue Shopping -->
                                <a href="{{ route('catalog.index') }}" class="mt-6 flex items-center justify-center gap-2 w-full py-3 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Tambah Produk Lain
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.public>
