<x-layouts.app>
    <div x-data="{
        showAccountModal: false,
        showTransactionModal: false,
        txType: 'in',
        activeFilter: '',
        startDate: '{{ $startDate }}',
        endDate: '{{ $endDate }}'
    }">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Buku Kas & Keuangan</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pantau arus kas masuk dan keluar dari semua rekening.</p>
            </div>
            <div class="flex gap-2 flex-wrap md:flex-nowrap">
                <button @click="showAccountModal = true" class="px-4 py-2 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 text-sm font-semibold rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    + Rekening
                </button>
                <button @click="txType = 'in'; showTransactionModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm shadow-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    Kas Masuk
                </button>
                <button @click="txType = 'out'; showTransactionModal = true" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors flex items-center gap-1.5 shadow-sm shadow-red-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    Kas Keluar
                </button>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-md bg-red-50 text-red-800 text-sm border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Saldo Kas Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="glass-card rounded-md p-4 border-t-4 border-primary-500 cursor-pointer"
                 @click="activeFilter = ''; $dispatch('filter-changed')"
                 :class="activeFilter === '' ? 'ring-2 ring-primary-500 bg-primary-50/50 dark:bg-primary-900/10' : ''">
                <div class="text-xs font-semibold text-gray-500 mb-1">TOTAL SEMUA KAS</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($accounts->sum('balance'), 0, ',', '.') }}</div>
            </div>
            @foreach($accounts as $acc)
            <div class="glass-card rounded-md p-4 border border-gray-200 dark:border-gray-800 cursor-pointer hover:border-primary-300 transition-colors"
                 @click="activeFilter = '{{ $acc->id }}'; $dispatch('filter-changed')"
                 :class="activeFilter === '{{ $acc->id }}' ? 'ring-2 ring-primary-500 bg-primary-50/50 dark:bg-primary-900/10' : ''">
                <div class="flex justify-between items-center mb-1">
                    <div class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase flex items-center gap-1.5">
                        @if($acc->type === 'bank')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                        @elseif($acc->type === 'e-wallet')
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        @else
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        @endif
                        {{ $acc->name }}
                    </div>
                </div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">Rp {{ number_format($acc->balance, 0, ',', '.') }}</div>
            </div>
            @endforeach
        </div>

        <div class="hidden" 
             @filter-changed.window="htmx.ajax('GET', '{{ route('finance.index') }}?account_id=' + activeFilter + '&start_date=' + startDate + '&end_date=' + endDate, '#finance-content')">
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-md p-4 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Filter Periode:</span>
            </div>
            <div class="flex flex-1 flex-col md:flex-row items-center gap-3">
                <input type="date" x-model="startDate" @change="$dispatch('filter-changed')" class="w-full md:w-auto px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                <span class="text-gray-500 text-sm">s/d</span>
                <input type="date" x-model="endDate" @change="$dispatch('filter-changed')" class="w-full md:w-auto px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div class="flex justify-end w-full md:w-auto">
                <a :href="`{{ route('finance.print') }}?account_id=${activeFilter}&start_date=${startDate}&end_date=${endDate}`" target="_blank" class="px-4 py-2 w-full md:w-auto bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-200 dark:text-gray-900 text-white text-sm font-semibold rounded-lg transition-colors flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak Laporan
                </a>
            </div>
        </div>

        <div id="finance-content">
            @include('finance.content')
        </div>

        <!-- Modal Tambah Rekening -->
        <div x-show="showAccountModal" class="relative z-[60]" style="display: none;">
            <div x-show="showAccountModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showAccountModal" 
                         @click.outside="showAccountModal = false"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-md bg-white dark:bg-gray-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 dark:border-gray-800">
                        <form action="{{ route('finance.accounts.store') }}" method="POST">
                            @csrf
                            <div class="px-4 pb-4 pt-5 sm:p-4 sm:pb-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Tambah Rekening Baru</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Rekening</label>
                                        <input type="text" name="name" required placeholder="Misal: BCA Operasional" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Jenis Rekening</label>
                                        <select name="type" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                            <option value="cash">Kas Tunai</option>
                                            <option value="bank">Bank Transfer</option>
                                            <option value="e-wallet">E-Wallet (OVO, Gopay, dll)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Saldo Awal (Rp)</label>
                                        <input type="number" name="balance" value="0" min="0" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-4">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Simpan</button>
                                <button type="button" @click="showAccountModal = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Transaksi Manual (Masuk/Keluar) -->
        <div x-show="showTransactionModal" class="relative z-[60]" style="display: none;">
            <div x-show="showTransactionModal" x-transition.opacity class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div x-show="showTransactionModal" 
                         @click.outside="showTransactionModal = false"
                         x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                         x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                         class="relative transform overflow-hidden rounded-md bg-white dark:bg-gray-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 dark:border-gray-800">
                        <form action="{{ route('finance.transactions.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="type" x-model="txType">
                            
                            <!-- Header Modal Dynamic -->
                            <div class="px-4 py-2.5 sm:px-4 border-b border-gray-100 dark:border-gray-800"
                                 :class="txType === 'in' ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-red-50 dark:bg-red-500/10'">
                                <h3 class="text-lg font-bold"
                                    :class="txType === 'in' ? 'text-emerald-800 dark:text-emerald-400' : 'text-red-800 dark:text-red-400'">
                                    <span x-text="txType === 'in' ? 'Catat Pemasukan Lainnya' : 'Catat Pengeluaran (Beban/Biaya)'"></span>
                                </h3>
                            </div>

                            <div class="px-4 pb-4 pt-5 sm:p-4">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Rekening Kas</label>
                                        <select name="account_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->name }} (Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nominal (Rp)</label>
                                        <input type="number" name="amount" min="1" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Keterangan / Tujuan</label>
                                        <input type="text" name="description" required placeholder="Misal: Bayar Listrik, Gaji Karyawan..." class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-4">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto"
                                        :class="txType === 'in' ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-red-600 hover:bg-red-500'">Simpan</button>
                                <button type="button" @click="showTransactionModal = false" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 sm:mt-0 sm:w-auto">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

