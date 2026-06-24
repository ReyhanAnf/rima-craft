<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Faktur Penjualan</h2>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-6 pb-24 space-y-6">
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-md p-4 border border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-4">
        <div>
            <div class="text-xs text-gray-500 mb-1">Tgl Faktur</div>
            <div class="font-semibold text-sm text-gray-900 dark:text-white">{{ $sale->date->format('d M Y') }}</div>
        </div>
        <div>
            <div class="text-xs text-gray-500 mb-1">Pengiriman</div>
            <select name="shipping_status" 
                    hx-patch="{{ route('sales.update-status', $sale) }}" 
                    hx-target="#drawer-content" 
                    class="text-[10px] font-bold border rounded outline-none focus:ring-1 focus:ring-primary-500 uppercase tracking-wider px-1 py-0.5 cursor-pointer
                    {{ $sale->shipping_status === 'delivered' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : '' }}
                    {{ $sale->shipping_status === 'shipped' ? 'bg-blue-50 border-blue-200 text-blue-700' : '' }}
                    {{ $sale->shipping_status === 'pending' ? 'bg-gray-100 border-gray-300 text-gray-700' : '' }}">
                <option value="pending" {{ $sale->shipping_status === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="shipped" {{ $sale->shipping_status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ $sale->shipping_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
            </select>
        </div>
        <div class="col-span-2 mt-1 pt-3 border-t border-gray-200 dark:border-gray-800">
            <div class="text-xs text-gray-500 mb-1">Pelanggan</div>
            <div class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                {{ $sale->customer ? $sale->customer->name : $sale->customer_name }}
                @if(!$sale->customer)
                    <span class="text-[9px] bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Non-Member</span>
                @endif
            </div>
            @if($sale->customer_phone || ($sale->customer && $sale->customer->phone))
            <div class="text-xs text-gray-500 mt-0.5">{{ $sale->customer ? $sale->customer->phone : $sale->customer_phone }}</div>
            @endif
        </div>
    </div>

    <div>
        <div class="flex justify-between items-end mb-3">
            <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Item Pembelian</h4>
            <select name="payment_status" 
                    hx-patch="{{ route('sales.update-status', $sale) }}" 
                    hx-target="#drawer-content" 
                    class="text-[10px] font-bold border rounded outline-none focus:ring-1 focus:ring-primary-500 uppercase tracking-wider px-1 py-0.5 cursor-pointer
                    {{ $sale->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : '' }}
                    {{ $sale->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700' : '' }}
                    {{ $sale->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700' : '' }}">
                <option value="unpaid" {{ $sale->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="partial" {{ $sale->payment_status === 'partial' ? 'selected' : '' }}>Partial</option>
                <option value="paid" {{ $sale->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
            </select>
        </div>
        <div class="space-y-2">
            @foreach($sale->items as $item)
            <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</div>
                    <div class="text-[10px] text-gray-500">{{ $item->qty }} pcs x Rp {{ number_format($item->price, 0, ',', '.') }}</div>
                </div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="mt-3 space-y-1.5 text-xs text-gray-500 text-right">
            <div class="flex justify-end gap-4">
                <span>Subtotal:</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($sale->shipping_fee > 0)
            <div class="flex justify-end gap-4">
                <span>Ongkir (+):</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($sale->shipping_fee, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($sale->discount > 0)
            <div class="flex justify-end gap-4 text-emerald-600">
                <span>Diskon (-):</span>
                <span class="font-semibold">Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
            </div>
            @endif
        </div>
        
        <div class="flex justify-between items-center pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
            <div class="font-bold text-gray-900 dark:text-white">Grand Total</div>
            <div class="font-bold text-primary-600 dark:text-primary-400 text-lg">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div class="mt-6 pt-6 border-t border-dashed border-gray-200 dark:border-gray-800">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider">Riwayat Pembayaran</h4>
                @if($sale->payment_status !== 'paid')
                <button type="button" @click="$dispatch('open-payment-modal', { type: 'Sale', id: {{ $sale->id }}, total: {{ $sale->grand_total }}, paid: {{ $sale->payments->sum('amount') }} })" class="px-3 py-1.5 bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-500/10 dark:text-primary-400 rounded-lg text-xs font-bold transition">
                    + Terima Dana
                </button>
                @endif
            </div>
            
            <div class="space-y-2">
                @forelse($sale->payments as $payment)
                <div class="flex justify-between items-center text-sm border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $payment->date->format('d M Y') }}</div>
                        <div class="text-[10px] text-gray-500">Masuk ke: {{ $payment->account->name }}</div>
                    </div>
                    <div class="font-bold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                </div>
                @empty
                <div class="text-xs text-gray-500 text-center py-2">Belum ada pembayaran</div>
                @endforelse
                
                <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">Total Diterima</div>
                    <div class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($sale->payments->sum('amount'), 0, ',', '.') }}</div>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">Sisa Tagihan</div>
                    <div class="text-sm font-bold text-red-600 dark:text-red-400">Rp {{ number_format($sale->grand_total - $sale->payments->sum('amount'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

