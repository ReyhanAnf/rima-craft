{{-- resources/views/catalog/partials/product-filter.blade.php --}}
<div class="mb-16">
    <form
        x-data="{ activeFilter: 'semua' }"
        hx-get="/katalog/filter"
        hx-target="#products-grid"
        hx-swap="innerHTML"
        hx-indicator="#filter-loading"
        class="flex flex-col gap-4 items-center"
    >
        {{-- Input Search --}}
        <div class="relative w-full max-w-md">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input
                type="search"
                name="search"
                placeholder="Cari produk..."
                hx-trigger="keyup changed delay:400ms from:this"
                hx-include="closest form"
                class="w-full pl-10 pr-4 py-3 bg-white dark:bg-[#0d0d0d] border border-gray-200 dark:border-white/10 rounded-full text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 dark:focus:border-amber-500 transition-all"
            />
        </div>

        {{-- Tombol Filter Stok --}}
        <div class="flex items-center gap-2 flex-wrap justify-center">
            @foreach([
                'semua'    => 'Semua',
                'tersedia' => 'Tersedia',
                'habis'    => 'Habis',
            ] as $value => $label)
                <button
                    type="submit"
                    name="stock"
                    value="{{ $value }}"
                    @click="activeFilter = '{{ $value }}'"
                    :class="activeFilter === '{{ $value }}'
                        ? 'bg-amber-500 text-white border-amber-500 shadow-lg shadow-amber-500/20'
                        : 'bg-transparent dark:bg-transparent text-gray-600 dark:text-gray-400 border-gray-200 dark:border-white/10 hover:border-gray-400 dark:hover:border-white/30'"
                    class="px-5 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest border transition-all duration-300 cursor-pointer"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </form>

    {{-- HTMX loading indicator --}}
    <div id="filter-loading" class="htmx-indicator flex justify-center mt-8">
        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-600 font-medium">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Memuat...</span>
        </div>
    </div>
</div>
