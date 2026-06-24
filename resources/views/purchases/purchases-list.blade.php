<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Tgl Transaksi</th>
                <th scope="col" class="px-4 py-3 font-semibold">Supplier</th>
                <th scope="col" class="px-4 py-3 font-semibold">Total</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($purchases as $purchase)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5">{{ $purchase->date->format('d M Y') }}</td>
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                    {{ $purchase->supplier ? $purchase->supplier->name : $purchase->supplier_name }}
                    @if(!$purchase->supplier)
                        <span class="text-[10px] text-gray-400 ml-1">(Non-Member)</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 font-semibold text-primary-700 dark:text-primary-400">
                    Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                        {{ $purchase->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $purchase->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-400' : '' }}
                        {{ $purchase->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                        {{ $purchase->payment_status }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <button hx-get="{{ route('purchases.show', $purchase) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline mr-2">Detail</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada transaksi pembelian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($purchases as $purchase)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex flex-col cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('purchases.show', $purchase) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $purchase->supplier ? $purchase->supplier->name : $purchase->supplier_name }}</h4>
                <div class="text-[10px] text-gray-500">{{ $purchase->date->format('d M Y') }}</div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                        {{ $purchase->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $purchase->payment_status === 'partial' ? 'bg-orange-50 border-orange-200 text-orange-700 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-400' : '' }}
                        {{ $purchase->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                {{ $purchase->payment_status }}
            </span>
        </div>
        <div class="text-xs font-bold text-primary-700 dark:text-primary-400">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada transaksi pembelian.</div>
    @endforelse
</div>

@if($purchases->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#purchases-list" hx-swap="innerHTML">
    {{ $purchases->links('pagination::tailwind') }}
</div>
@endif

