<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <div>
        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Detail Produksi</h2>
        <div class="text-xs text-gray-500 mt-0.5">{{ $production->date->format('d M Y') }}</div>
    </div>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-6 pb-24 space-y-6">
    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-md p-4 border border-gray-100 dark:border-gray-800">
        <div class="text-xs text-gray-500 mb-1">Catatan Produksi</div>
        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $production->notes ?: 'Tidak ada catatan' }}</div>
    </div>

    <!-- Bahan Baku Dipakai -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">1. Bahan Baku Diserap (-)</h4>
        <div class="space-y-2">
            @foreach($production->materials as $item)
            <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2">
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">{{ $item->material->name }}</div>
                    <div class="text-[10px] text-gray-500">{{ $item->qty }} {{ $item->material->unit }} x Rp {{ number_format($item->cost_per_unit, 0, ',', '.') }}</div>
                </div>
                <div class="font-semibold text-gray-900 dark:text-white">
                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Biaya Tambahan -->
    <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2 pt-2">
        <div>
            <div class="font-medium text-gray-900 dark:text-white">Biaya Tambahan / Upah Tukang</div>
        </div>
        <div class="font-semibold text-gray-900 dark:text-white">
            Rp {{ number_format($production->additional_cost, 0, ',', '.') }}
        </div>
    </div>

    <!-- Produk Dihasilkan -->
    <div>
        <h4 class="text-xs font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3 mt-4">2. Produk Dihasilkan (+)</h4>
        <div class="space-y-2">
            @foreach($production->results as $item)
            <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-800 pb-2">
                <div>
                    <div class="font-medium text-emerald-600 dark:text-emerald-400">+ {{ $item->qty }}x {{ $item->product->name }}</div>
                    <div class="text-[10px] text-gray-500">Alokasi HPP: Rp {{ number_format($item->allocated_cost_per_unit, 0, ',', '.') }} / item</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="flex justify-between items-center pt-4 mt-4 border-t border-gray-200 dark:border-gray-800">
        <div class="font-bold text-gray-900 dark:text-white">Grand Total HPP</div>
        <div class="font-bold text-primary-600 dark:text-primary-400 text-lg">Rp {{ number_format($production->grand_total_cost, 0, ',', '.') }}</div>
    </div>
</div>

