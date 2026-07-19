<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Arus Kas - {{ config('settings.business_name', 'Rima Craft') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { background-color: white !important; color: black !important; }
            .no-print { display: none !important; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
            th { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; }
            @page { margin: 1cm; }
        }
        body { background-color: white; color: black; font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="p-4 sm:p-8 max-w-5xl mx-auto">

    <!-- Print Action Buttons (Hidden in Print) -->
    <div class="no-print flex justify-between md:justify-end mb-8 gap-3">
        <button onclick="closeReport()" class="flex-1 md:flex-none px-4 py-2 bg-gray-200 text-gray-800 rounded-xl text-sm font-semibold hover:bg-gray-300">Tutup</button>
        <a href="{{ route('finance.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'account_id' => $account?->id]) }}" class="flex-1 md:flex-none justify-center px-4 py-2 bg-amber-600 text-white rounded-xl text-sm font-semibold hover:bg-amber-700 flex items-center gap-2" download>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Download PDF
        </a>
    </div>

    <!-- Header Laporan -->
    <div class="text-center mb-8 border-b-2 border-gray-900 pb-6">
        <h1 class="text-2xl sm:text-3xl font-bold uppercase tracking-widest">{{ config('settings.business_name', 'Rima Craft') }}</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Laporan Keuangan - Arus Kas (Cash Flow)</p>
    </div>

    <!-- Informasi Periode -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-3 mb-6">
        <div>
            <table class="text-xs sm:text-sm">
                <tr>
                    <td class="py-1 font-semibold border-none !p-0 pr-4">Periode</td>
                    <td class="py-1 border-none !p-0">: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold border-none !p-0 pr-4">Rekening Kas</td>
                    <td class="py-1 border-none !p-0">: {{ $account ? $account->name : 'Semua Rekening' }}</td>
                </tr>
            </table>
        </div>
        <div class="text-left sm:text-right text-[10px] sm:text-xs text-gray-500">
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}
        </div>
    </div>

    <!-- Summary Box -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 text-sm">
        <div class="p-4 border border-gray-200 rounded-xl bg-emerald-50/50">
            <div class="text-emerald-700 uppercase text-[10px] font-bold tracking-wider mb-1">Total Pemasukan</div>
            <div class="text-lg sm:text-xl font-extrabold text-emerald-800">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="p-4 border border-gray-200 rounded-xl bg-rose-50/50">
            <div class="text-rose-700 uppercase text-[10px] font-bold tracking-wider mb-1">Total Pengeluaran</div>
            <div class="text-lg sm:text-xl font-extrabold text-rose-800">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="p-4 border border-gray-200 rounded-xl bg-gray-100/70">
            <div class="text-gray-600 uppercase text-[10px] font-bold tracking-wider mb-1">Arus Kas Bersih (Net)</div>
            <div class="text-xl sm:text-2xl font-black text-gray-900">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Transaksi Table (Desktop Only) -->
    <div class="hidden md:block overflow-x-auto border border-gray-200 rounded-xl mb-12">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4 text-left font-bold text-gray-700 w-12">No</th>
                    <th class="py-3 px-4 text-left font-bold text-gray-700 w-24">Tanggal</th>
                    <th class="py-3 px-4 text-left font-bold text-gray-700 w-32">Rekening</th>
                    <th class="py-3 px-4 text-left font-bold text-gray-700">Keterangan</th>
                    <th class="py-3 px-4 text-right font-bold text-gray-700 w-32">Masuk (Rp)</th>
                    <th class="py-3 px-4 text-right font-bold text-gray-700 w-32">Keluar (Rp)</th>
                    <th class="py-3 px-4 text-right font-bold text-gray-700 w-32">Saldo Akhir (Rp)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($ledgers as $index => $ledger)
                    <tr>
                        <td class="py-3 px-4 text-gray-500 text-xs">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 text-gray-900 whitespace-nowrap text-xs">{{ $ledger->date->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 text-gray-900 text-xs">{{ $ledger->account->name }}</td>
                        <td class="py-3 px-4 text-gray-600 text-xs font-semibold">{{ $ledger->description }}</td>
                        <td class="py-3 px-4 text-right text-emerald-600 font-bold text-xs">
                            {{ $ledger->type === 'in' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right text-red-650 font-bold text-xs">
                            {{ $ledger->type === 'out' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-3 px-4 text-right text-gray-900 font-extrabold text-xs">
                            {{ number_format($ledger->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-500 italic">Tidak ada transaksi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Transaction Cards -->
    <div class="block md:hidden border border-gray-200 rounded-2xl divide-y divide-gray-150 mb-12 bg-white overflow-hidden shadow-sm">
        @forelse($ledgers as $index => $ledger)
            <div class="p-4 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] text-gray-400">{{ $ledger->date->format('d/m/Y') }}</p>
                        <h4 class="text-xs font-bold text-gray-900 mt-0.5 leading-relaxed">
                            {{ $ledger->description }}
                        </h4>
                    </div>
                    <span class="shrink-0 text-xs font-black {{ $ledger->type === 'in' ? 'text-emerald-600' : 'text-red-500' }}">
                        {{ $ledger->type === 'in' ? '+' : '-' }} Rp {{ number_format($ledger->amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-[10px] pt-1">
                    <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 font-bold uppercase text-[9px]">
                        {{ $ledger->account->name }}
                    </span>
                    <span class="text-gray-500 font-bold">
                        Saldo: Rp {{ number_format($ledger->balance_after, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-400 italic">Tidak ada transaksi pada periode ini.</div>
        @endforelse
    </div>

    <!-- Tanda Tangan Section -->
    <div class="flex justify-end mt-12">
        <div class="text-center w-48">
            <p class="text-xs text-gray-600 mb-16">Mengetahui,<br>Admin Keuangan</p>
            <p class="text-xs font-bold border-b border-gray-400 pb-1">{{ auth()->user()->name ?? 'Administrator' }}</p>
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        function closeReport() {
            if (window.opener) {
                window.close();
            } else {
                window.location.href = "{{ route('finance.index') }}";
            }
        }
    </script>
</body>
</html>
