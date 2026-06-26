<x-layouts.app>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div x-data="{ tab: '{{ $range === 'custom' || request()->has('range') ? 'analytics' : 'general' }}' }" class="space-y-6">
        
        <!-- Dashboard Header & Title -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-5">
            <div>
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-1 flex items-center gap-3">
                    Halo, {{ auth()->user()->name }}!
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Berikut adalah performa operasional & keuangan {{ config('settings.business_name', 'Rima Craft') }}.</p>
            </div>

            <!-- Tab Switcher -->
            <div class="inline-flex bg-gray-100 dark:bg-black p-1 rounded-xl border border-gray-200 dark:border-gray-800">
                <button @click="tab = 'general'" 
                        :class="tab === 'general' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm border border-gray-200/50 dark:border-gray-800' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                    Ringkasan Umum
                </button>
                <button @click="tab = 'analytics'" 
                        :class="tab === 'analytics' ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm border border-gray-200/50 dark:border-gray-800' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-2 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Detail Analitik & Grafik
                </button>
            </div>
        </div>

        <!-- Interactive Date Range Filter Panel -->
        <div class="bg-white dark:bg-black p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <form action="{{ route('dashboard') }}" method="GET" class="w-full flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mr-2">Periode:</span>
                    @foreach(['today' => 'Hari Ini', 'last_7_days' => '7 Hari', 'last_30_days' => '30 Hari', 'this_month' => 'Bulan Ini', 'this_year' => 'Tahun Ini', 'custom' => 'Kustom'] as $key => $label)
                        <button type="submit" name="range" value="{{ $key }}"
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all cursor-pointer {{ $range === $key ? 'bg-primary-600 text-white shadow' : 'bg-gray-50 hover:bg-gray-100 text-gray-600 dark:bg-gray-900 dark:hover:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-800/80' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
                
                @if($range === 'custom')
                <div class="flex items-center gap-2 animate-fade-in self-start md:self-auto">
                    <input type="hidden" name="range" value="custom">
                    <div class="flex items-center gap-1">
                        <label class="text-[10px] text-gray-400 uppercase font-bold mr-1">Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate->toDateString() }}" 
                               class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500 text-gray-800 dark:text-gray-200">
                    </div>
                    <span class="text-gray-400 text-xs">-</span>
                    <div class="flex items-center gap-1">
                        <label class="text-[10px] text-gray-400 uppercase font-bold mr-1">Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate->toDateString() }}" 
                               class="px-2.5 py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500 text-gray-800 dark:text-gray-200">
                    </div>
                    <button type="submit" class="px-3.5 py-1.5 text-xs font-bold bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors cursor-pointer shadow-sm">
                        Terapkan
                    </button>
                </div>
                @else
                <div class="text-[11px] font-medium text-gray-400 dark:text-gray-500">
                    Rentang: <span class="font-bold text-gray-600 dark:text-gray-300">{{ $startDate->format('d M Y') }}</span> s/d <span class="font-bold text-gray-600 dark:text-gray-300">{{ $endDate->format('d M Y') }}</span>
                </div>
                @endif
            </form>
        </div>

        <!-- ==================== FINANCIAL KPI CARDS GRID ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Revenue (Penjualan) -->
            <div class="bg-white dark:bg-black rounded-xl p-5 border-l-4 border-emerald-500 border border-gray-200 dark:border-gray-850 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Pendapatan (Omset)</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                    </div>
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-500/10 rounded-lg text-emerald-600 dark:text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500">
                    Nilai kotor transaksi penjualan dibuat
                </div>
            </div>

            <!-- Purchases (Belanja Bahan) -->
            <div class="bg-white dark:bg-black rounded-xl p-5 border-l-4 border-rose-500 border border-gray-200 dark:border-gray-850 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Total Pengeluaran (Belanja)</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">Rp {{ number_format($totalPurchases, 0, ',', '.') }}</h3>
                    </div>
                    <div class="p-2 bg-rose-50 dark:bg-rose-500/10 rounded-lg text-rose-600 dark:text-rose-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500">
                    Belanja bahan baku & operasional dibuat
                </div>
            </div>

            <!-- Gross Profit (Laba Kotor Estimasi) -->
            <div class="bg-white dark:bg-black rounded-xl p-5 border-l-4 {{ $grossProfit >= 0 ? 'border-teal-500' : 'border-red-500' }} border border-gray-200 dark:border-gray-850 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Estimasi Laba Kotor</p>
                        <h3 class="text-2xl font-black mt-1 {{ $grossProfit >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $grossProfit < 0 ? '-' : '' }}Rp {{ number_format(abs($grossProfit), 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="p-2 {{ $grossProfit >= 0 ? 'bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' }} rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-500">
                    Nilai transaksi Penjualan - Pembelian
                </div>
            </div>

            <!-- Net Cash Flow (Arus Kas Bersih) -->
            <div class="bg-white dark:bg-black rounded-xl p-5 border-l-4 border-indigo-500 border border-gray-200 dark:border-gray-850 hover:shadow-md transition duration-300">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Arus Kas Aktual</p>
                        @php $netCashflow = $cashInflow - $cashOutflow; @endphp
                        <h3 class="text-2xl font-black mt-1 {{ $netCashflow >= 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $netCashflow < 0 ? '-' : '' }}Rp {{ number_format(abs($netCashflow), 0, ',', '.') }}
                        </h3>
                    </div>
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg text-indigo-600 dark:text-indigo-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                </div>
                <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold truncate">
                    Masuk: Rp {{ number_format($cashInflow, 0, ',', '.') }} | Keluar: Rp {{ number_format($cashOutflow, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- ==================== SECONDARY CASH/DEBT/CREDIT OVERVIEW ==================== -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Safe/Cash Balance -->
            <div class="bg-white dark:bg-black p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total Saldo Kas Sekarang</p>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white">Rp {{ number_format($totalKas, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <a href="{{ route('finance.index') }}" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 rounded-lg transition" title="Buka Buku Kas">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 00-2 2v2m10-10V6a2 2 0 00-2-2h-4m10 6h4a2 2 0 012 2v4a2 2 0 01-2 2h-4m-6 0h6m-6 0v2m0-2v-4"></path></svg>
                </a>
            </div>

            <!-- Total Receivables (Piutang Aktif) -->
            <div class="bg-white dark:bg-black p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Piutang Pelanggan (Outstanding)</p>
                        <h4 class="text-lg font-black text-amber-600 dark:text-amber-400">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <a href="{{ route('sales.index') }}" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 rounded-lg transition" title="Buka Penjualan">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <!-- Total Payables (Utang Aktif) -->
            <div class="bg-white dark:bg-black p-4 rounded-xl border border-gray-200 dark:border-gray-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Utang Supplier (Belum Bayar)</p>
                        <h4 class="text-lg font-black text-rose-600 dark:text-rose-400">Rp {{ number_format($totalPayables, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <a href="{{ route('purchases.index') }}" class="p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400 hover:text-gray-600 rounded-lg transition" title="Buka Pembelian">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        <!-- ==================== TAB 1: GENERAL OPERATIONAL SUMMARY ==================== -->
        <div x-show="tab === 'general'" x-transition.opacity.duration.300ms class="space-y-6">
            
            <!-- Operational Alerts Row (Low Stock, Outstanding Sales/Purchases) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- 1. Bahan Baku Menipis -->
                <div class="bg-white dark:bg-black rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col h-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-sm uppercase tracking-wide">
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                            Stok Bahan Menipis
                        </h3>
                        <span class="px-2 py-0.5 text-[10px] font-black bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-md border border-red-200 dark:border-red-500/20">
                            {{ $lowStockMaterialsCount }} Bahan
                        </span>
                    </div>

                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[260px] pr-1">
                        @forelse($lowStockMaterialsLimit as $mat)
                        <div class="flex justify-between items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/80">
                            <div class="flex-1 min-w-0 mr-2">
                                <p class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ $mat->name }}</p>
                                <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">Minimal Stok: {{ $mat->min_stock }} {{ $mat->unit }}</div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-black bg-red-50 border border-red-200 text-red-600 dark:bg-red-950 dark:border-red-900 dark:text-red-400">
                                    {{ $mat->current_stock }} {{ $mat->unit }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                            <svg class="w-10 h-10 mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Semua stok bahan baku aman!</span>
                        </div>
                        @endforelse
                    </div>

                    @if($lowStockMaterialsCount > 0)
                    <a href="{{ route('materials.index') }}" class="mt-4 block w-full text-center py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold hover:bg-gray-100 dark:hover:bg-gray-850 transition">
                        Kelola Bahan Baku
                    </a>
                    @endif
                </div>

                <!-- 2. Piutang Pelanggan Jatuh Tempo -->
                <div class="bg-white dark:bg-black rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col h-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-sm uppercase tracking-wide">
                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Piutang Pelanggan
                        </h3>
                        <span class="px-2 py-0.5 text-[10px] font-black bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded-md border border-amber-200 dark:border-amber-500/20">
                            Outstanding
                        </span>
                    </div>

                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[260px] pr-1">
                        @forelse($outstandingSales as $sale)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/80 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-sm text-gray-950 dark:text-white">INV-{{ str_pad((string)$sale->id, 5, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-[140px]">{{ $sale->customer->name ?? $sale->customer_name ?? 'Pelanggan Umum' }}</p>
                                @if($sale->due_date)
                                <p class="text-[10px] text-rose-500 font-semibold mt-1">Tempo: {{ $sale->due_date->format('d M Y') }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-black text-sm text-red-600 dark:text-red-400">Rp {{ number_format($sale->outstanding_amount, 0, ',', '.') }}</p>
                                <button @click="$dispatch('open-payment-modal', { type: 'Sale', id: {{ $sale->id }}, total: {{ (float)$sale->grand_total }}, paid: {{ (float)($sale->grand_total - $sale->outstanding_amount) }} })"
                                        class="mt-1.5 px-3 py-1 text-[10px] font-bold bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition cursor-pointer">
                                    Bayar
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                            <svg class="w-10 h-10 mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Semua piutang pelanggan lunas!</span>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. Utang Belanja Jatuh Tempo -->
                <div class="bg-white dark:bg-black rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col h-full">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2 text-sm uppercase tracking-wide">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Utang Supplier
                        </h3>
                        <span class="px-2 py-0.5 text-[10px] font-black bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-md border border-rose-200 dark:border-rose-500/20">
                            Belum Bayar
                        </span>
                    </div>

                    <div class="space-y-3 flex-1 overflow-y-auto max-h-[260px] pr-1">
                        @forelse($outstandingPurchases as $pur)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800/80 flex justify-between items-center">
                            <div>
                                <p class="font-bold text-sm text-gray-950 dark:text-white">PUR-{{ str_pad((string)$pur->id, 5, '0', STR_PAD_LEFT) }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 truncate max-w-[140px]">{{ $pur->supplier->name ?? $pur->supplier_name ?? 'Supplier Umum' }}</p>
                                @if($pur->due_date)
                                <p class="text-[10px] text-rose-500 font-semibold mt-1">Tempo: {{ $pur->due_date->format('d M Y') }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="font-black text-sm text-rose-600 dark:text-rose-400">Rp {{ number_format($pur->outstanding_amount, 0, ',', '.') }}</p>
                                <button @click="$dispatch('open-payment-modal', { type: 'Purchase', id: {{ $pur->id }}, total: {{ (float)$pur->total_amount }}, paid: {{ (float)($pur->total_amount - $pur->outstanding_amount) }} })"
                                        class="mt-1.5 px-3 py-1 text-[10px] font-bold bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition cursor-pointer">
                                    Bayar
                                </button>
                            </div>
                        </div>
                        @empty
                        <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                            <svg class="w-10 h-10 mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">Semua utang supplier lunas!</span>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Activity lists (2 Columns Grid) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Recent Sales List -->
                <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-gray-50/50 dark:bg-black/50">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wide">5 Penjualan Terakhir Periode Ini</h3>
                        <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-primary-600 hover:text-primary-700">Lihat Semua</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800 flex-1">
                        @forelse($recentSales as $sale)
                        <div class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                            <div>
                                <div class="font-bold text-sm text-gray-900 dark:text-white">INV-{{ str_pad((string)$sale->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $sale->customer->name ?? $sale->customer_name ?? 'Pelanggan Umum' }} &bull; {{ $sale->date->format('d M Y') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-sm text-gray-955 dark:text-white">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</div>
                                @if($sale->payment_status === 'paid')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border bg-emerald-50 border-emerald-250 text-emerald-700 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 mt-1">LUNAS</span>
                                @elseif($sale->payment_status === 'partial')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border bg-amber-50 border-amber-250 text-amber-700 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-400 mt-1">CICILAN</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold border bg-red-50 border-red-250 text-red-700 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400 mt-1">BELUM LUNAS</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-sm text-gray-500">Belum ada transaksi penjualan di periode ini.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Pending & Production Activity Info -->
                <div class="bg-white dark:bg-black rounded-xl p-5 border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col h-full">
                    <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wide mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Aktivitas Produksi Kerajinan
                    </h3>
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-amber-50/50 dark:bg-amber-500/5 border border-amber-100 dark:border-amber-900/30 rounded-xl text-center">
                            <span class="block text-xs font-bold text-gray-500 uppercase">Produksi Antrian</span>
                            <span class="block text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $pendingProductions }}</span>
                        </div>
                        <div class="p-4 bg-teal-50/50 dark:bg-teal-500/5 border border-teal-100 dark:border-teal-900/30 rounded-xl text-center">
                            <span class="block text-xs font-bold text-gray-500 uppercase">Selesai Periode Ini</span>
                            <span class="block text-2xl font-black text-teal-600 dark:text-teal-400 mt-1">{{ $completedProductions }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed mb-4 flex-1">
                        Gunakan menu Produksi untuk memantau penggunaan bahan baku, biaya pengrajin, dan hasil produksi kerajinan anyaman secara mendetail.
                    </p>

                    <a href="{{ route('productions.index') }}" class="block w-full text-center py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-bold transition shadow-sm cursor-pointer">
                        Mulai / Kelola Produksi Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 2: DETAILED ANALYTICS & CHARTS ==================== -->
        <div x-show="tab === 'analytics'" x-transition.opacity.duration.300ms style="display: none;" class="space-y-6">
            
            <!-- Charts Section (Grid) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- 1. Revenue & Purchase Area Chart -->
                <div class="lg:col-span-2 bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 flex flex-col">
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wide">Tren Keuangan: Penjualan vs Pembelian</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Membandingkan nilai kotor omset penjualan dengan pengeluaran belanja bahan baku.</p>
                    </div>
                    <div id="revenue-chart" class="flex-1 -ml-3"></div>
                </div>

                <!-- 2. Top Selling Products Donut -->
                <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 flex flex-col">
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm uppercase tracking-wide">Proporsi Produk Terlaris</h3>
                        <p class="text-[11px] text-gray-400 mt-0.5">Persentase kontribusi kuantitas produk yang paling banyak terjual.</p>
                    </div>
                    
                    <div class="flex-1 flex items-center justify-center">
                        @if(count($topProducts) > 0)
                            <div id="top-products-chart" class="w-full"></div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 text-gray-450 dark:text-gray-600">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                <span class="text-xs font-semibold">Belum ada data penjualan produk</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Leaderboards & Details Row (Three Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Top Customers -->
                <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-black/50">
                        <h3 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            Pelanggan Terbesar
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($topCustomers as $index => $cust)
                        <div class="p-4 flex items-center gap-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                            <div class="w-7 h-7 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs font-black">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $cust->name }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ $cust->phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-xs text-gray-900 dark:text-white">Rp {{ number_format($cust->total_spent, 0, ',', '.') }}</p>
                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest block mt-0.5">Belanja</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 text-center text-xs text-gray-500">Belum ada pelanggan tercatat.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Top Suppliers -->
                <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-black/50">
                        <h3 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wide flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path d="M13 7H7v6h6V7z"></path><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h10V3a1 1 0 112 0v1h1a2 2 0 012 2v11a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2h1V3a1 1 0 011-1zm12 5H5v11h12V7z" clip-rule="evenodd"></path></svg>
                            Supplier Terbesar
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($topSuppliers as $index => $sup)
                        <div class="p-4 flex items-center gap-3.5 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                            <div class="w-7 h-7 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xs font-black">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $sup->name }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ $sup->phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-xs text-gray-900 dark:text-white">Rp {{ number_format($sup->total_received, 0, ',', '.') }}</p>
                                <span class="text-[8px] font-bold text-gray-400 uppercase tracking-widest block mt-0.5">Pasokan</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 text-center text-xs text-gray-500">Belum ada supplier tercatat.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Detailed Top Products List Table -->
                <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-black/50">
                        <h3 class="font-bold text-gray-900 dark:text-white text-xs uppercase tracking-wide">
                            Top Produk Terlaris
                        </h3>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800 flex-1">
                        @forelse($topProducts as $index => $item)
                        <div class="p-4 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                            <div class="w-7 h-7 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs font-black">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0 mr-2">
                                <p class="font-bold text-xs text-gray-900 dark:text-white truncate">{{ $item->product->name ?? 'Produk Terhapus' }}</p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Rp {{ number_format((float)($item->product->base_price ?? 0), 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-xs text-primary-600 dark:text-primary-400">{{ $item->total_sold }} Pcs</p>
                                <p class="text-[9px] font-semibold text-gray-400 mt-0.5">Rp {{ number_format((float)$item->total_revenue, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="p-6 text-center text-xs text-gray-500">Belum ada data penjualan produk.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Init ApexCharts Scripts -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const chartData = @json($chartData);
                    
                    // 1. Revenue & Purchase Line/Area Chart
                    var revenueOptions = {
                        series: [
                            {
                                name: 'Penjualan (Omset)',
                                data: chartData.sales
                            },
                            {
                                name: 'Pembelian (Belanja)',
                                data: chartData.purchases
                            }
                        ],
                        chart: {
                            type: 'area',
                            height: 350,
                            fontFamily: 'Inter, sans-serif',
                            toolbar: { show: false },
                            zoom: { enabled: false }
                        },
                        colors: ['#10b981', '#f43f5e'], // Emerald & Rose
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.35,
                                opacityTo: 0.02,
                                stops: [0, 95, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 3 },
                        xaxis: {
                            categories: chartData.categories,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: {
                                    colors: '#9ca3af',
                                    fontSize: '11px'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9ca3af', fontSize: '11px' },
                                formatter: function (value) {
                                    return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                                }
                            }
                        },
                        grid: {
                            borderColor: document.documentElement.classList.contains('dark') ? '#1f2937' : '#f3f4f6',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: true } },
                            yaxis: { lines: { show: true } },
                            padding: { top: 0, right: 10, bottom: 0, left: 10 }
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: {
                                colors: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#4b5563'
                            }
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                        }
                    };

                    var revenueChart = new ApexCharts(document.querySelector("#revenue-chart"), revenueOptions);
                    revenueChart.render();
                    
                    // 2. Top Selling Products Donut Chart
                    @if(count($topProducts) > 0)
                    var donutOptions = {
                        series: @json($topProducts->map(fn($item) => (int)$item->total_sold)->toArray()),
                        chart: {
                            type: 'donut',
                            height: 300,
                            fontFamily: 'Inter, sans-serif'
                        },
                        labels: @json($topProducts->map(fn($item) => $item->product->name ?? 'Produk Terhapus')->toArray()),
                        colors: ['#10b981', '#6366f1', '#f59e0b', '#ec4899', '#14b8a6'],
                        legend: {
                            position: 'bottom',
                            fontSize: '11px',
                            labels: {
                                colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#4b5563'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function (val) {
                                return val.toFixed(1) + "%";
                            },
                            style: {
                                fontSize: '10px'
                            }
                        },
                        stroke: {
                            show: true,
                            width: 2,
                            colors: [document.documentElement.classList.contains('dark') ? '#000' : '#fff']
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%'
                                }
                            }
                        },
                        theme: {
                            mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                        }
                    };
                    var donutChart = new ApexCharts(document.querySelector("#top-products-chart"), donutOptions);
                    donutChart.render();
                    @endif
                    
                    // Dynamic Theme Updater
                    window.addEventListener('theme-changed', (e) => {
                        const newMode = e.detail.theme;
                        const borderColor = newMode === 'dark' ? '#1f2937' : '#f3f4f6';
                        const textLegendColor = newMode === 'dark' ? '#d1d5db' : '#4b5563';
                        const donutStrokeColor = newMode === 'dark' ? '#000' : '#fff';
                        
                        revenueChart.updateOptions({
                            theme: { mode: newMode },
                            grid: { borderColor: borderColor },
                            legend: { labels: { colors: textLegendColor } }
                        });

                        @if(count($topProducts) > 0)
                        donutChart.updateOptions({
                            theme: { mode: newMode },
                            legend: { labels: { colors: textLegendColor } },
                            stroke: { colors: [donutStrokeColor] }
                        });
                        @endif
                    });
                });
            </script>
        </div>
    </div>
</x-layouts.app>
