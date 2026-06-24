<x-layouts.app>
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Selamat Datang, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portal Pelanggan - Kelola pesanan dan profil Anda</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass-card rounded-lg p-5 border-l-4 border-primary-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total Pesanan</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalOrders }}</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-lg p-5 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Menunggu Pengiriman</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $pendingOrders }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-lg p-5 border-l-4 border-emerald-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Selesai</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalOrders - $pendingOrders }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pesanan Terbaru</h2>
                <a href="{{ route('customer.orders') }}" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Lihat Semua →
                </a>
            </div>
            
            @if($recentOrders->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($recentOrders as $order)
                <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">INV-{{ $order->id }}</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                    {{ $order->shipping_status === 'delivered' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400' : 
                                       ($order->shipping_status === 'shipped' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : 
                                       'bg-orange-100 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400') }}">
                                    {{ ucfirst($order->shipping_status) }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full 
                                    {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 
                                       ($order->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400' : 
                                       'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->items->sum('qty') }} item</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Belum ada pesanan</p>
                <a href="{{ route('catalog.index') }}" class="inline-block mt-3 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Mulai Belanja
                </a>
            </div>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('catalog.index') }}" class="glass-card rounded-lg p-5 border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Lihat Katalog</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Jelajahi produk kami</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('customer.profile') }}" class="glass-card rounded-lg p-5 border border-gray-200 dark:border-gray-800 hover:border-primary-300 dark:hover:border-primary-700 transition-colors group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Profil Saya</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi pribadi</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-layouts.app>
