<!-- Desktop Table -->
<div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-800">
            <tr>
                <th scope="col" class="px-4 py-3 font-semibold w-32">Tanggal</th>
                <th scope="col" class="px-4 py-3 font-semibold w-40">Rekening</th>
                <th scope="col" class="px-4 py-3 font-semibold">Keterangan Transaksi</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Kas Keluar</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right">Kas Masuk</th>
                <th scope="col" class="px-4 py-3 font-semibold text-right bg-gray-100 dark:bg-gray-800">Saldo Akhir</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($ledgers as $ledger)
            <tr class="bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                <td class="px-4 py-2.5 text-xs">{{ $ledger->date->format('d M Y') }}</td>
                <td class="px-4 py-2.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border uppercase tracking-wider bg-gray-100 border-gray-200 text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                        {{ $ledger->account->name }}
                    </span>
                </td>
                <td class="px-4 py-2.5 text-gray-900 dark:text-white font-medium">
                    <div class="flex items-center gap-2">
                        @if($ledger->reference_type)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400" title="Sistem Otomatis">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </span>
                        @endif
                        {{ $ledger->description }}
                    </div>
                </td>
                <td class="px-4 py-2.5 text-right text-red-600 dark:text-red-400 font-medium">
                    {{ $ledger->type === 'out' ? '- Rp ' . number_format($ledger->amount, 0, ',', '.') : '' }}
                </td>
                <td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400 font-medium">
                    {{ $ledger->type === 'in' ? '+ Rp ' . number_format($ledger->amount, 0, ',', '.') : '' }}
                </td>
                <td class="px-4 py-2.5 text-right font-bold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-800/50">
                    Rp {{ number_format($ledger->balance_after, 0, ',', '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-6 text-center text-xs text-gray-500">Belum ada mutasi kas.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
    @forelse ($ledgers as $ledger)
    <div class="p-4 bg-white/50 dark:bg-gray-900/50 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
        <div class="flex justify-between items-start mb-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $ledger->type === 'in' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400' : 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400' }}">
                    @if($ledger->type === 'in')
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white line-clamp-1">{{ $ledger->description }}</h4>
                    <div class="text-[10px] text-gray-500">{{ $ledger->date->format('d M Y') }} &bull; {{ $ledger->account->name }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-bold {{ $ledger->type === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $ledger->type === 'in' ? '+' : '-' }} Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                </div>
                <div class="text-[10px] font-medium text-gray-500 mt-0.5">Saldo: Rp {{ number_format($ledger->balance_after, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    @empty
    <div class="p-4 text-center text-xs text-gray-500">Belum ada mutasi kas.</div>
    @endforelse
</div>

@if($ledgers->hasPages())
<div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50" hx-boost="true" hx-target="#ledgers-list" hx-swap="innerHTML">
    {{ $ledgers->links('pagination::tailwind') }}
</div>
@endif

