<x-layouts.app>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-5">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Daftar Produksi & Makloon</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau proses konversi bahan baku menjadi produk jadi.</p>
        </div>
        <div class="flex w-full md:w-auto gap-3">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input type="search" name="search" placeholder="Cari catatan..." 
                       hx-get="{{ route('productions.index') }}" 
                       hx-target="#productions-list" 
                       hx-trigger="keyup changed delay:500ms, search"
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 outline-none transition-shadow">
            </div>
            <a href="{{ route('productions.create') }}" 
               class="shrink-0 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Catat Produksi
            </a>
        </div>
    </div>

    <div id="productions-list" class="glass-card rounded-md overflow-hidden border border-gray-200 dark:border-gray-800">
        @include('productions.productions-list', ['productions' => $productions])
    </div>
</x-layouts.app>

