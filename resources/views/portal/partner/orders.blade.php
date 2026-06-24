<x-layouts.app>
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Pesanan Partner</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Semua pesanan dengan harga reseller</p>
            </div>
            <a href="{{ route('partner.dashboard') }}" class="text-sm text-primary-600 hover:text-primary-700">← Kembali</a>
        </div>

        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
            @if($orders->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($orders as $order)
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-bold">INV-{{ $order->id }}</span>
                            <p class="text-xs text-gray-500">{{ $order->date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $order->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t">{{ $orders->links() }}</div>
            @else
            <div class="p-8 text-center text-gray-500">Belum ada pesanan</div>
            @endif
        </div>
    </div>
</x-layouts.app>
