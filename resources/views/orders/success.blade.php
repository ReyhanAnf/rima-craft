<x-layouts.public>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 py-12 px-4">
        <div class="max-w-3xl mx-auto">
            
            <!-- Success Header -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-8 mb-6 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full mb-4">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Pesanan Berhasil Dibuat!</h1>
                <p class="text-gray-600 dark:text-gray-400">Terima kasih, {{ $order->customer_name }}!</p>
            </div>

            <!-- Order Details -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-8 mb-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detail Pesanan</h2>
                    <span class="px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-400 rounded-full text-sm font-semibold">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nomor Pesanan</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $order->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="space-y-3 mb-6">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $item['name'] }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item['qty'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                            <p class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($item['qty'] * $item['price'], 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>

                <!-- Total -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-base font-bold text-gray-900 dark:text-white">Total Pembayaran</span>
                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Instructions -->
            @php
                $paymentMethod = \App\Models\PaymentMethod::where('code', $order->payment_method)->first();
            @endphp

            @if($paymentMethod && $order->payment_method !== 'cod')
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl shadow-xl border-2 border-amber-200 dark:border-amber-800 p-8 mb-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Instruksi Pembayaran</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $paymentMethod->name }}</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl p-6 space-y-4">
                        @if($paymentMethod->account_number)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Nomor Rekening / Telepon</p>
                                <div class="flex items-center gap-3">
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $paymentMethod->account_number }}</p>
                                    <button onclick="navigator.clipboard.writeText('{{ $paymentMethod->account_number }}'); this.innerHTML = '✓ Tersalin!'" 
                                            class="px-3 py-1 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-sm font-semibold transition-colors">
                                        Salin
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($paymentMethod->account_name)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Atas Nama</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $paymentMethod->account_name }}</p>
                            </div>
                        @endif

                        @if($paymentMethod->description)
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Keterangan</p>
                                <p class="text-base text-gray-700 dark:text-gray-300">{{ $paymentMethod->description }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 p-4 bg-amber-100 dark:bg-amber-900/40 rounded-xl">
                        <p class="text-sm text-amber-900 dark:text-amber-200">
                            <strong>Penting:</strong> Silakan lakukan pembayaran dalam waktu <strong>24 jam</strong>. 
                            Setelah transfer, pesanan Anda akan segera diproses.
                        </p>
                    </div>
                </div>
            @elseif($order->payment_method === 'cod')
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl shadow-xl border-2 border-green-200 dark:border-green-800 p-8 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Bayar di Tempat (COD)</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Siapkan uang tunai saat barang diterima</p>
                        </div>
                    </div>
                    <p class="text-base text-gray-700 dark:text-gray-300">
                        Pesanan Anda akan dikirim ke alamat yang diberikan. Bayar langsung ke kurir saat barang tiba.
                    </p>
                </div>
            @endif

            <!-- Customer Info -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 p-8 mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Informasi Pengiriman</h2>
                <div class="space-y-3">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nama</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Telepon</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $order->customer_phone }}</p>
                        </div>
                        @if($order->customer_email)
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $order->customer_email }}</p>
                        </div>
                        @endif
                    </div>
                    @if($order->customer_address)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Alamat</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $order->customer_address }}</p>
                    </div>
                    @endif
                    @if($order->notes)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Catatan</p>
                        <p class="text-gray-700 dark:text-gray-300 italic">{{ $order->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="{{ route('catalog.index') }}" 
                   class="flex-1 flex items-center justify-center gap-2 py-4 px-6 bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-700 rounded-xl font-bold text-gray-700 dark:text-gray-300 hover:border-gray-400 dark:hover:border-gray-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Lanjutkan Belanja
                </a>
                <a href="https://wa.me/{{ config('settings.business_phone', '6281234567890') }}?text=Halo, saya ingin konfirmasi pesanan {{ $order->order_number }}" 
                   target="_blank"
                   class="flex-1 flex items-center justify-center gap-2 py-4 px-6 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-xl font-bold shadow-lg shadow-[#25D366]/30 transition-all">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Konfirmasi via WhatsApp
                </a>
            </div>

            <!-- Info Note -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Simpan nomor pesanan Anda: <strong class="text-gray-900 dark:text-white">{{ $order->order_number }}</strong>
                </p>
            </div>
        </div>
    </div>
</x-layouts.public>
