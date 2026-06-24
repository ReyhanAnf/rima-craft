<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold">Nama Bahan</th>
                <th scope="col" class="px-4 py-3 font-semibold">Stok Saat Ini</th>
                <th scope="col" class="px-4 py-3 font-semibold">Stok Min</th>
                <th scope="col" class="px-4 py-3 font-semibold">Harga Beli Akhir</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($materials as $material)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-white">{{ $material->name }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $material->current_stock <= $material->min_stock ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : 'bg-primary-50 border-primary-200 text-primary-700 dark:bg-primary-500/10 dark:border-primary-500/20 dark:text-primary-400' }}">
                        {{ $material->current_stock }} {{ $material->unit }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-xs">{{ $material->min_stock }} {{ $material->unit }}</td>
                <td class="px-4 py-2.5 text-xs">Rp {{ number_format($material->last_buy_price, 0, ',', '.') }}</td>
                <td class="px-4 py-2.5 text-right text-xs">
                    <button hx-get="{{ route('materials.edit', $material) }}" hx-target="#drawer-content" hx-swap="innerHTML" @click="$dispatch('open-drawer')" class="font-medium text-primary-600 dark:text-primary-400 hover:underline mr-3">Edit</button>
                    <button hx-delete="{{ route('materials.destroy', $material) }}" hx-confirm="Hapus {{ $material->name }}?" hx-target="#materials-list" hx-swap="innerHTML" class="font-medium text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-xs text-gray-500">Data tidak ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($materials as $material)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors flex justify-between items-center cursor-pointer active:bg-gray-100 dark:active:bg-gray-800"
         hx-get="{{ route('materials.edit', $material) }}" 
         hx-target="#drawer-content" 
         hx-swap="innerHTML" 
         @click="$dispatch('open-drawer')">
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $material->name }}</h4>
            <div class="flex items-center gap-2 mt-1.5">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $material->current_stock <= $material->min_stock ? 'bg-red-50 border-red-200 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400' : 'bg-primary-50 border-primary-200 text-primary-700 dark:bg-primary-500/10 dark:border-primary-500/20 dark:text-primary-400' }}">
                    {{ $material->current_stock }} {{ $material->unit }}
                </span>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Rp {{ number_format($material->last_buy_price, 0, ',', '.') }}</span>
            </div>
        </div>
        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Data tidak ditemukan.</div>
    @endforelse
</div>

@if($materials->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#materials-list" hx-swap="innerHTML">
    {{ $materials->links('pagination::tailwind') }}
</div>
@endif

