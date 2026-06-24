<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Detail Pembelian</h2>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-6 pb-24 space-y-6">
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-md p-4 border border-gray-100 dark:border-gray-800">
        <div class="text-xs text-gray-500 mb-1">Tanggal Transaksi</div>
        <div class="font-semibold text-gray-900 dark:text-white">{{ $purchase->date->format('d F Y') }}</div>
        
        <div class="mt-3 text-xs text-gray-500 mb-1">Supplier</div>
        <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            {{ $purchase->supplier ? $purchase->supplier->name : $purchase->supplier_name }}
            @if(!$purchase->supplier)
                <span class="text-[9px] bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Manual</span>
            @endif
        </div>
        @if($purchase->supplier_phone || ($purchase->supplier && $purchase->supplier->phone))
        <div class="text-xs text-gray-500 mt-0.5">{{ $purchase->supplier ? $purchase->supplier->phone : $purchase->supplier_phone }}</div>
        @endif

        <div class="mt-3 text-xs text-gray-500 mb-1">Status Pembayaran</div>
        <div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                {{ $purchase->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                {{ $purchase->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-400' : '' }}
                {{ $purchase->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                {{ $purchase->payment_status }}
            </span>
        </div>
    </div>

    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Item Pembelian</h4>
        <div class="space-y-2">
            @foreach($purchase->items as $item)
            <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $item->material->name }}</div>
                    <div class="text-[10px] text-gray-500">{{ $item->qty }} {{ $item->material->unit }} x Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                </div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->qty * $item->price, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
        <div class="flex justify-between items-center pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="font-bold text-gray-900 dark:text-white">Grand Total</div>
            <div class="font-bold text-primary-600 dark:text-primary-400 text-lg">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div class="mt-6 pt-6 border-t border-dashed border-gray-200 dark:border-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Riwayat Pembayaran</h4>
                @if($purchase->payment_status !== 'paid')
                <button type="button" @click="$dispatch('open-payment-modal', { type: 'Purchase', id: {{ $purchase->id }}, total: {{ $purchase->total_amount }}, paid: {{ $purchase->payments->sum('amount') }} })" class="px-3 py-1.5 bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400 rounded-lg text-xs font-bold transition">
                    + Bayar
                </button>
                @endif
            </div>
            
            <div class="space-y-2">
                @forelse($purchase->payments as $payment)
                <div class="flex justify-between items-center text-sm border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $payment->date->format('d M Y') }}</div>
                        <div class="text-[10px] text-gray-500">{{ $payment->account->name }}</div>
                    </div>
                    <div class="font-bold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-500 text-center py-2">Belum ada pembayaran</div>
                @endforelse
                
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">Total Dibayar</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($purchase->payments->sum('amount'), 0, ',', '.') }}</div>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">Sisa Tagihan</div>
                    <div class="text-sm font-bold text-red-600 dark:text-red-400">Rp {{ number_format($purchase->total_amount - $purchase->payments->sum('amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

