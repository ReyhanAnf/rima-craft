<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Tgl & No. Pesanan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Pelanggan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Metode</th>
                <th scope="col" class="px-4 py-3 font-semibold">Grand Total</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status Pesanan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Pembayaran</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($orders as $order)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5">
                    <div class="font-medium text-gray-900 dark:text-white">{{ $order->order_number }}</div>
                    <div class="text-[10px] text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</div>
                </td>
                <td class="px-4 py-2.5">
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $order->customer_name }}
                        @if($order->user_id)
                            <span class="text-[9px] bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 border border-primary-200 dark:border-primary-800 px-1 py-0.5 rounded font-semibold ml-1">Member</span>
                        @else
                            <span class="text-[9px] bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-1 py-0.5 rounded ml-1">Guest</span>
                        @endif
                    </div>
                    <div class="text-[10px] text-gray-400">{{ $order->customer_phone }}</div>
                </td>
                <td class="px-4 py-2.5">
                    <div class="font-medium text-gray-700 dark:text-gray-300 uppercase text-xs">{{ $order->payment_method }}</div>
                    <div class="text-[10px] text-gray-400 capitalize">via {{ $order->order_method }}</div>
                </td>
                <td class="px-4 py-2.5 font-semibold text-amber-600 dark:text-amber-400">
                    Rp {{ number_format($order->total, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                        {{ $order->status === 'pending' ? 'bg-gray-100 border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' : '' }}
                        {{ $order->status === 'confirmed' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                        {{ $order->status === 'processing' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-400' : '' }}
                        {{ $order->status === 'shipped' ? 'bg-cyan-50 border-cyan-200 text-cyan-700 dark:bg-cyan-500/10 dark:border-cyan-500/20 dark:text-cyan-400' : '' }}
                        {{ $order->status === 'completed' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $order->status === 'cancelled' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                        {{ $order->status }}
                    </span>
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider
                        {{ $order->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $order->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}
                        {{ $order->payment_status === 'refunded' ? 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-400' : '' }}">
                        {{ $order->payment_status }}
                    </span>
                    @if($order->payment_proof)
                        <span class="ml-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-bold" title="Bukti Transfer Tersedia">📎 Bukti</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <button hx-get="{{ route('orders.show', $order) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-bold text-primary-600 dark:text-primary-400 hover:underline">Detail</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada pesanan online.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($orders as $order)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex flex-col cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('orders.show', $order) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->customer_name }}</h4>
                <div class="text-[10px] text-gray-500">{{ $order->order_number }} &bull; {{ $order->created_at->format('d M, H:i') }}</div>
            </div>
            <div class="flex flex-col gap-1 items-end">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                            {{ $order->status === 'pending' ? 'bg-gray-100 border-gray-300 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400' : '' }}
                            {{ $order->status === 'confirmed' ? 'bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400' : '' }}
                            {{ $order->status === 'processing' ? 'bg-indigo-50 border-indigo-200 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-400' : '' }}
                            {{ $order->status === 'shipped' ? 'bg-cyan-50 border-cyan-200 text-cyan-700 dark:bg-cyan-500/10 dark:border-cyan-500/20 dark:text-cyan-400' : '' }}
                            {{ $order->status === 'completed' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                            {{ $order->status === 'cancelled' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}">
                    {{ $order->status }}
                </span>
            </div>
        </div>
        <div class="flex justify-between items-end mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                        {{ $order->payment_status === 'paid' ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400' : '' }}
                        {{ $order->payment_status === 'unpaid' ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : '' }}
                        {{ $order->payment_status === 'refunded' ? 'bg-amber-50 border-amber-200 text-amber-700 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-400' : '' }}">
                Bayar: {{ $order->payment_status }}
            </span>
            <div class="flex items-center gap-2">
                @if($order->payment_proof)
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">📎 Bukti</span>
                @endif
                <div class="text-xs font-bold text-amber-600 dark:text-amber-400">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada pesanan online.</div>
    @endforelse
</div>

@if($orders->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#orders-list" hx-swap="innerHTML">
    {{ $orders->links('pagination::tailwind') }}
</div>
@endif
