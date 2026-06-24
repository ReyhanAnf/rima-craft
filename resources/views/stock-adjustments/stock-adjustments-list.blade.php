<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Waktu</th>
                <th scope="col" class="px-4 py-3 font-semibold">Tipe</th>
                <th scope="col" class="px-4 py-3 font-semibold">Item</th>
                <th scope="col" class="px-4 py-3 font-semibold text-center">Tercatat</th>
                <th scope="col" class="px-4 py-3 font-semibold text-center">Fisik</th>
                <th scope="col" class="px-4 py-3 font-semibold text-center">Selisih</th>
                <th scope="col" class="px-4 py-3 font-semibold">Alasan</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Oleh</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($adjustments as $adj)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5 text-xs">{{ $adj->created_at->format('d M Y H:i') }}</td>
                <td class="px-4 py-2.5">
                    @if($adj->adjustable_type === 'App\Models\Material')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400">Bahan</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border bg-purple-50 border-purple-200 text-purple-700 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400">Produk</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $adj->adjustable->name ?? 'Terhapus' }}</td>
                <td class="px-4 py-2.5 text-center text-xs text-gray-500">{{ floatval($adj->previous_stock) }}</td>
                <td class="px-4 py-2.5 text-center font-bold text-gray-900 dark:text-white">{{ floatval($adj->actual_stock) }}</td>
                <td class="px-4 py-2.5 text-center">
                    @if($adj->quantity_difference > 0)
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">+{{ floatval($adj->quantity_difference) }}</span>
                    @else
                        <span class="text-xs font-bold text-red-600 dark:text-red-400">{{ floatval($adj->quantity_difference) }}</span>
                    @endif
                </td>
                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $adj->reason }}</td>
                <td class="px-4 py-2.5 text-right text-xs text-gray-500">{{ $adj->user->name ?? 'Sistem' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada riwayat penyesuaian stok.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($adjustments as $adj)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
        <div class="flex justify-between items-start mb-2">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    @if($adj->adjustable_type === 'App\Models\Material')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border bg-blue-50 border-blue-200 text-blue-700 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400">Bahan</span>
                    @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border bg-purple-50 border-purple-200 text-purple-700 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400">Produk</span>
                    @endif
                    <span class="text-[10px] text-gray-400">{{ $adj->created_at->format('d M Y H:i') }}</span>
                </div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $adj->adjustable->name ?? 'Terhapus' }}</h4>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-gray-500">Selisih</div>
                @if($adj->quantity_difference > 0)
                    <div class="text-sm font-bold text-emerald-600 dark:text-emerald-400">+{{ floatval($adj->quantity_difference) }}</div>
                @else
                    <div class="text-sm font-bold text-red-600 dark:text-red-400">{{ floatval($adj->quantity_difference) }}</div>
                @endif
            </div>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-2 rounded">
            <div><span class="font-medium text-gray-600 dark:text-gray-300">Tercatat:</span> {{ floatval($adj->previous_stock) }} â†’ <span class="font-medium text-gray-600 dark:text-gray-300">Fisik:</span> {{ floatval($adj->actual_stock) }}</div>
            <div class="mt-1"><span class="font-medium text-gray-600 dark:text-gray-300">Alasan:</span> {{ $adj->reason }}</div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada riwayat penyesuaian stok.</div>
    @endforelse
</div>

@if($adjustments->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#stock-adjustments-list" hx-swap="innerHTML">
    {{ $adjustments->links('pagination::tailwind') }}
</div>
@endif

