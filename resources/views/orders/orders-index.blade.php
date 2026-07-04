<x-layouts.app>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Daftar Pesanan Online</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola dan proses seluruh pesanan pelanggan yang masuk dari website.</p>
        </div>
        <div class="flex w-full md:w-auto gap-3">
            <button type="button" @click="$dispatch('toggle-filters')" 
                    class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filter
            </button>
        </div>
    </div>

    {{-- Filter Panel --}}
    <div x-data="{ showFilters: false }" 
         @toggle-filters.window="showFilters = !showFilters"
         x-show="showFilters"
         x-transition
         class="mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm"
         style="display: none;">
        <form hx-get="{{ route('orders.index') }}" 
              hx-target="#orders-list" 
              hx-trigger="change from:input delay:300ms, submit"
              hx-push-url="true"
              class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            {{-- Date Range --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Mulai</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Akhir</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            {{-- Order Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Pesanan</label>
                <select name="status" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Dikonfirmasi (Confirmed)</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses (Processing)</option>
                    <option value="shipped" {{ request('status') === 'shipped' ? 'selected' : '' }}>Dikirim (Shipped)</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai (Completed)</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
                </select>
            </div>

            {{-- Payment Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Status Pembayaran</label>
                <select name="payment_status" 
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar (Unpaid)</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                    <option value="partial" {{ request('payment_status') === 'partial' ? 'selected' : '' }}>DP / Partial</option>
                    <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Dikembalikan (Refunded)</option>
                </select>
            </div>

            {{-- Search --}}
            <div class="lg:col-span-4">
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5">Cari Pesanan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari No. Pesanan, Nama, Email, atau HP Pelanggan..." 
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>
            </div>

            {{-- Clear Filters --}}
            <div class="lg:col-span-4 flex justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('orders.index') }}" 
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                    Reset Filter
                </a>
            </div>
        </form>
    </div>

    {{-- Active Filters Indicator --}}
    @if(request()->hasAny(['date_from', 'date_to', 'status', 'payment_status', 'search']))
    <div class="mb-3 flex flex-wrap items-center gap-2 text-xs">
        <span class="text-gray-500 dark:text-gray-400">Filter aktif:</span>
        @if(request('date_from') || request('date_to'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-md border border-primary-200 dark:border-primary-800">
            📅 {{ request('date_from', '...') }} → {{ request('date_to', '...') }}
        </span>
        @endif
        @if(request('status'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-md border border-amber-200 dark:border-amber-800">
            Status: {{ ucfirst(request('status')) }}
        </span>
        @endif
        @if(request('payment_status'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 rounded-md border border-emerald-200 dark:border-emerald-800">
            Pembayaran: {{ ucfirst(request('payment_status')) }}
        </span>
        @endif
        @if(request('search'))
        <span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 rounded-md border border-purple-200 dark:border-purple-800">
            🔍 "{{ request('search') }}"
        </span>
        @endif
        <a href="{{ route('orders.index') }}"
           class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 underline">
            Hapus Semua
        </a>
    </div>
    @endif

    <div id="orders-list" 
         hx-get="{{ route('orders.index') }}" 
         hx-trigger="reload-orders-list from:body" 
         hx-target="this" 
         hx-swap="innerHTML"
         class="glass-card rounded-md overflow-hidden border border-gray-200 dark:border-gray-800">
        @include('orders.orders-list', ['orders' => $orders])
    </div>
</x-layouts.app>
