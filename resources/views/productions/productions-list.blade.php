<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold w-32">Tanggal</th>
                <th scope="col" class="px-4 py-3 font-semibold w-48">Produk Dihasilkan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Total HPP</th>
                <th scope="col" class="px-4 py-3 font-semibold">Catatan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($productions as $prod)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5">{{ $prod->date->format('d M Y') }}</td>
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">
                    <div class="flex flex-col gap-0.5">
                        @foreach($prod->results as $res)
                        <span class="text-xs">{{ $res->qty }}x {{ $res->product->name }}</span>
                        @endforeach
                    </div>
                </td>
                <td class="px-4 py-2.5 font-semibold text-primary-700 dark:text-primary-400">
                    Rp {{ number_format($prod->grand_total_cost, 0, ',', '.') }}
                </td>
                <td class="px-4 py-2.5 text-xs truncate max-w-[200px]">
                    {{ $prod->notes ?: '-' }}
                </td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400">
                        {{ $prod->status }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <button hx-get="{{ route('productions.show', $prod) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">Detail</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada catatan produksi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($productions as $prod)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex flex-col cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('productions.show', $prod) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')">
        <div class="flex justify-between items-start mb-2">
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                    @foreach($prod->results as $res)
                        {{ $res->qty }}x {{ $res->product->name }}@if(!$loop->last), @endif
                    @endforeach
                </h4>
                <div class="text-[10px] text-gray-500 mt-0.5">{{ $prod->date->format('d M Y') }}</div>
            </div>
            <div class="flex flex-col gap-1 items-end">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400">
                    {{ $prod->status }}
                </span>
            </div>
        </div>
        <div class="flex justify-between items-end mt-1">
            <span class="text-xs text-gray-500 italic truncate w-1/2">{{ $prod->notes ?: '-' }}</span>
            <div class="text-xs font-bold text-primary-700 dark:text-primary-400">Rp {{ number_format($prod->grand_total_cost, 0, ',', '.') }}</div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada catatan produksi.</div>
    @endforelse
</div>

@if($productions->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#productions-list" hx-swap="innerHTML">
    {{ $productions->links('pagination::tailwind') }}
</div>
@endif

