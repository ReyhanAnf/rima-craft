<x-layouts.app>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Bahan Baku</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data inventori bahan baku.</p>
        </div>
        <div class="flex w-full md:w-auto gap-3">
            <button type="button" @click="$dispatch('toggle-filters')" 
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            <button hx-get="{{ route('materials.create') }}" 
                    hx-target="#drawer-content" 
                    hx-swap="innerHTML"
                    @click="$dispatch('open-drawer')"
                    class="shrink-0 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div x-data="{ showFilters: false }" 
         @toggle-filters.window="showFilters = !showFilters"
         x-show="showFilters"
         x-transition
         class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <form hx-get="{{ route('materials.index') }}" 
              hx-target="#materials-list" 
              hx-trigger="change from:input delay:300ms, submit"
              hx-push-url="true"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            
            {{-- Stock Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Stok</label>
                <select name="stock_status" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="available" {{ request('stock_status') === 'available' ? 'selected' : '' }}>Tersedia (> Min)</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Menipis (≤ Min)</option>
                    <option value="empty" {{ request('stock_status') === 'empty' ? 'selected' : '' }}>Habis (0)</option>
                </select>
            </div>

            {{-- Max Stock --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Max. Stok</label>
                <input type="number" name="max_stock" value="{{ request('max_stock') }}" placeholder="∞" min="0"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            {{-- Search --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cari Bahan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama bahan..." 
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>

            {{-- Clear Filters --}}
            <div class="lg:col-span-3 flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="window.location.href='{{ route('materials.index') }}'" 
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Reset Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Active Filters Indicator --}}
    @if(request()->hasAny(['stock_status', 'max_stock', 'search']))
    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="text-gray-500 dark:text-gray-400">Filter aktif:</span>
        @if(request('stock_status'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-50 dark:bg-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-900/20 text-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-700 dark:text-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-400 rounded-md border border-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-200 dark:border-{{ request('stock_status') === 'available' ? 'emerald' : (request('stock_status') === 'low' ? 'orange' : 'red') }}-800">
            📦 {{ request('stock_status') === 'available' ? 'Tersedia' : (request('stock_status') === 'low' ? 'Menipis' : 'Habis') }}
        </span>
        @endif
        @if(request('search'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-md border border-purple-200 dark:border-purple-800">
            🔍 "{{ request('search') }}"
        </span>
        @endif
        <button onclick="window.location.href='{{ route('materials.index') }}'"
                class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline">
            Hapus Semua
        </button>
    </div>
    @endif

    <div id="materials-list" class="glass-card rounded-md overflow-hidden border border-gray-200 dark:border-gray-800">
        @include('materials.materials-list', ['materials' => $materials])
    </div>
</x-layouts.app>

