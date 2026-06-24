<x-layouts.app>
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Partner 🤝</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portal Reseller - Kelola pesanan dan tagihan Anda</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="glass-card rounded-lg p-5 border-l-4 border-primary-500">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Pesanan</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalOrders }}</p>
            </div>

            <div class="glass-card rounded-lg p-5 border-l-4 border-orange-500">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Menunggu Kirim</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $pendingOrders }}</p>
            </div>

            <div class="glass-card rounded-lg p-5 border-l-4 border-emerald-500">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Tagihan</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">Rp {{ number_format($totalBilling, 0, ',', '.') }}</p>
            </div>

            <div class="glass-card rounded-lg p-5 border-l-4 border-red-500">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Sisa Tagihan</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">Rp {{ number_format($outstandingBalance, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pesanan Terbaru</h2>
                <a href="{{ route('partner.orders') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Lihat Semua →</a>
            </div>
            
            @if($recentOrders->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($recentOrders as $order)
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-bold">INV-{{ $order->id }}</span>
                            <span class="ml-2 px-2 py-0.5 text-xs font-semibold rounded-full bg-{{ $order->shipping_status === 'delivered' ? 'emerald' : 'orange' }}-100 text-{{ $order->shipping_status === 'delivered' ? 'emerald' : 'orange' }}-700">
                                {{ ucfirst($order->shipping_status) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500">{{ $order->date->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center text-gray-500">Belum ada pesanan</div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('partner.billing') }}" class="glass-card rounded-lg p-5 border hover:border-red-300 transition-colors">
                <h3 class="font-semibold">💳 Lihat Tagihan</h3>
                <p class="text-sm text-gray-500">Cek status pembayaran</p>
            </a>
            <a href="{{ route('catalog.index') }}" class="glass-card rounded-lg p-5 border hover:border-primary-300 transition-colors">
                <h3 class="font-semibold">🛒 Order Baru</h3>
                <p class="text-sm text-gray-500">Harga reseller aktif</p>
            </a>
            <a href="{{ route('partner.profile') }}" class="glass-card rounded-lg p-5 border hover:border-blue-300 transition-colors">
                <h3 class="font-semibold">👤 Profil</h3>
                <p class="text-sm text-gray-500">Kelola informasi bisnis</p>
            </a>
        </div>
    </div>
</x-layouts.app>
