<x-layouts.app>
    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Stok</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sesuaikan stok fisik bahan baku dan produk jadi.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="$dispatch('toggle-filters')" 
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
            <button hx-get="{{ route('stock-adjustments.create') }}" 
                    hx-target="#drawer-content" 
                    hx-swap="innerHTML"
                    @click="$dispatch('open-drawer')"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Penyesuaian
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div x-data="{ showFilters: false }" 
         @toggle-filters.window="showFilters = !showFilters"
         x-show="showFilters"
         x-transition
         class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
        <form hx-get="{{ route('stock-adjustments.index') }}" 
              hx-target="#stock-adjustments-list" 
              hx-trigger="change from:input delay:300ms, submit"
              hx-push-url="true"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Adjustment Type --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tipe</label>
                <select name="type" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Semua Tipe</option>
                    <option value="add" {{ request('type') === 'add' ? 'selected' : '' }}>➕ Penambahan</option>
                    <option value="reduce" {{ request('type') === 'reduce' ? 'selected' : '' }}>➖ Pengurangan</option>
                </select>
            </div>

            {{-- Date From --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            {{-- Date To --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Akhir</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            {{-- User --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">User</label>
                <select name="user_id" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Semua User</option>
                    @foreach(\App\Models\User::orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Clear Filters --}}
            <div class="lg:col-span-4 flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <button type="button" @click="window.location.href='{{ route('stock-adjustments.index') }}'" 
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Reset Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Active Filters Indicator --}}
    @if(request()->hasAny(['type', 'date_from', 'date_to', 'user_id']))
    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="text-gray-500 dark:text-gray-400">Filter aktif:</span>
        @if(request('type'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-{{ request('type') === 'add' ? 'emerald' : 'red' }}-50 dark:bg-{{ request('type') === 'add' ? 'emerald' : 'red' }}-900/20 text-{{ request('type') === 'add' ? 'emerald' : 'red' }}-700 dark:text-{{ request('type') === 'add' ? 'emerald' : 'red' }}-400 rounded-md border border-{{ request('type') === 'add' ? 'emerald' : 'red' }}-200 dark:border-{{ request('type') === 'add' ? 'emerald' : 'red' }}-800">
            {{ request('type') === 'add' ? '➕' : '➖' }} {{ request('type') === 'add' ? 'Penambahan' : 'Pengurangan' }}
        </span>
        @endif
        @if(request('date_from') || request('date_to'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-md border border-blue-200 dark:border-blue-800">
            📅 {{ request('date_from', '...') }} → {{ request('date_to', '...') }}
        </span>
        @endif
        <button onclick="window.location.href='{{ route('stock-adjustments.index') }}'"
                class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline">
            Hapus Semua
        </button>
    </div>
    @endif

    <!-- Content Card -->
    <div class="bg-white dark:bg-gray-900 rounded-md border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden" id="stock-adjustments-list">
        @include('stock-adjustments.stock-adjustments-list', ['adjustments' => $adjustments])
    </div>
</x-layouts.app>

