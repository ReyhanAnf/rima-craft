<!-- Arus Kas Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="glass-card rounded-md p-4 border border-emerald-200 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-900/10">
        <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-1 uppercase flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            Total Pemasukan
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
    </div>
    <div class="glass-card rounded-md p-4 border border-red-200 dark:border-red-800 bg-red-50/30 dark:bg-red-900/10">
        <div class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1 uppercase flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
            Total Pengeluaran
        </div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
    </div>
    <div class="glass-card rounded-md p-4 border border-blue-200 dark:border-blue-800 bg-blue-50/30 dark:bg-blue-900/10">
        <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-1 uppercase flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Arus Kas Bersih (Net)
        </div>
        <div class="text-2xl font-bold {{ $netCashFlow < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
            Rp {{ number_format($netCashFlow, 0, ',', '.') }}
        </div>
    </div>
</div>

<div id="ledgers-list" class="glass-card rounded-md overflow-hidden border border-gray-200 dark:border-gray-800">
    @include('finance.ledgers-list', ['ledgers' => $ledgers])
</div>
