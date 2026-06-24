<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Tgl Penjualan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Pelanggan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Grand Total</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status Bayar</th>
                <th scope="col" class="px-4 py-3 font-semibold">Pengiriman</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($sales as $sale)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5">{{ $sale->date->format('d M Y') }}</td>
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                    {{ $sale->customer ? $sale->customer->name : $sale->customer_name }}
                    @if(!$sale->customer)
                        <span class="text-[10px] text-gray-400 ml-1">(Non-Member)</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 font-semibold text-primary-700 dark:text-primary-400">
                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                        {{ $sale->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $sale->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-400' : '' }}
                        {{ $sale->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                        {{ $sale->payment_status }}
                    </span>
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                        {{ $sale->shipping_status === 'delivered' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $sale->shipping_status === 'shipped' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                        {{ $sale->shipping_status === 'pending' ? 'bg-gray-100 border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' : '' }}">
                        {{ $sale->shipping_status }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <div class="flex items-center justify-end gap-3">
                        <button hx-get="{{ route('sales.show', $sale) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">Detail</button>
                        <a href="{{ route('sales.print', $sale) }}" target="_blank" class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" title="Cetak Invoice">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada faktur penjualan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($sales as $sale)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex flex-col cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('sales.show', $sale) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $sale->customer ? $sale->customer->name : $sale->customer_name }}</h4>
                <div class="text-[10px] text-gray-500">{{ $sale->date->format('d M Y') }}</div>
            </div>
            <div class="flex flex-col gap-1 items-end">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                            {{ $sale->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                            {{ $sale->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-400' : '' }}
                            {{ $sale->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                    {{ $sale->payment_status }}
                </span>
            </div>
        </div>
        <div class="flex justify-between items-end mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-medium border
                        {{ $sale->shipping_status === 'delivered' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $sale->shipping_status === 'shipped' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                        {{ $sale->shipping_status === 'pending' ? 'bg-gray-100 border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' : '' }}">
                Pengiriman: {{ $sale->shipping_status }}
            </span>
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.print', $sale) }}" target="_blank" @click.stop class="text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors" title="Cetak Invoice">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                </a>
                <div class="text-xs font-bold text-primary-700 dark:text-primary-400">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada faktur penjualan.</div>
    @endforelse
</div>

@if($sales->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#sales-list" hx-swap="innerHTML">
    {{ $sales->links('pagination::tailwind') }}
</div>
@endif

