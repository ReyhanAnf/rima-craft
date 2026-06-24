<x-layouts.app>
    <div class="max-w-6xl mx-auto">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tagihan & Pembayaran</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan tagihan Anda</p>
            </div>
            <a href="{{ route('partner.dashboard') }}" class="text-sm text-primary-600 hover:text-primary-700">← Kembali</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass-card rounded-lg p-5 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800">
                <p class="text-xs font-medium text-emerald-600 uppercase">Total Tagihan</p>
                <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400 mt-1">Rp {{ number_format($totalBilling, 0, ',', '.') }}</p>
            </div>
            <div class="glass-card rounded-lg p-5 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800">
                <p class="text-xs font-medium text-blue-600 uppercase">Sudah Dibayar</p>
                <p class="text-2xl font-bold text-blue-700 dark:text-blue-400 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
            </div>
            <div class="glass-card rounded-lg p-5 bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800">
                <p class="text-xs font-medium text-red-600 uppercase">Belum Dibayar</p>
                <p class="text-2xl font-bold text-red-700 dark:text-red-400 mt-1">Rp {{ number_format($totalBilling - $totalPaid, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
            @if($invoices->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($invoices as $invoice)
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-bold">INV-{{ $invoice->id }}</span>
                            <p class="text-xs text-gray-500">{{ $invoice->date->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $invoice->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-4 py-3 border-t">{{ $invoices->links() }}</div>
            @else
            <div class="p-8 text-center text-gray-500">Tidak ada tagihan</div>
            @endif
        </div>
    </div>
</x-layouts.app>
