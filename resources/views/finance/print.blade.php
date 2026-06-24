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
<body class="p-8 max-w-5xl mx-auto">

    <!-- Print Action Buttons (Hidden in Print) -->
    <div class="no-print flex justify-end mb-8 space-x-4">
        <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Tutup</button>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Dokumen
        </button>
    </div>

    <!-- Header Laporan -->
    <div class="text-center mb-8 border-b-2 border-gray-900 pb-6">
        <h1 class="text-3xl font-bold uppercase tracking-widest">{{ config('settings.business_name', 'Rima Craft') }}</h1>
        <p class="text-gray-500 text-sm mt-1">Laporan Keuangan - Arus Kas (Cash Flow)</p>
    </div>

    <!-- Informasi Periode -->
    <div class="flex justify-between items-end mb-6">
        <div>
            <table class="text-sm">
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
        <div class="text-right text-xs text-gray-500">
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i:s') }}
        </div>
    </div>

    <!-- Summary Box -->
    <div class="flex mb-8 border border-gray-200 rounded-md overflow-hidden text-sm">
        <div class="flex-1 p-4 border-r border-gray-200 bg-gray-50">
            <div class="text-gray-500 uppercase text-xs font-bold mb-1">Total Pemasukan</div>
            <div class="text-lg font-bold text-emerald-700">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
        </div>
        <div class="flex-1 p-4 border-r border-gray-200 bg-gray-50">
            <div class="text-gray-500 uppercase text-xs font-bold mb-1">Total Pengeluaran</div>
            <div class="text-lg font-bold text-red-700">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
        </div>
        <div class="flex-1 p-4 bg-gray-100">
            <div class="text-gray-600 uppercase text-xs font-bold mb-1">Arus Kas Bersih (Net)</div>
            <div class="text-xl font-bold text-gray-900">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</div>
        </div>
    </div>

    <!-- Transaksi Table -->
    <table class="w-full text-sm mb-12">
        <thead class="bg-gray-100 border-b-2 border-gray-300">
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
        <tbody>
            @forelse($ledgers as $index => $ledger)
                <tr class="border-b border-gray-200">
                    <td class="py-2 px-4 text-gray-600">{{ $index + 1 }}</td>
                    <td class="py-2 px-4 text-gray-900 whitespace-nowrap">{{ $ledger->date->format('d/m/Y') }}</td>
                    <td class="py-2 px-4 text-gray-900">{{ $ledger->account->name }}</td>
                    <td class="py-2 px-4 text-gray-600">{{ $ledger->description }}</td>
                    <td class="py-2 px-4 text-right text-emerald-600 font-medium">
                        {{ $ledger->type === 'in' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2 px-4 text-right text-red-600 font-medium">
                        {{ $ledger->type === 'out' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="py-2 px-4 text-right text-gray-900 font-semibold">
                        {{ number_format($ledger->balance_after, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-gray-500 italic">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Section -->
    <div class="flex justify-end mt-12">
        <div class="text-center w-48">
            <p class="text-sm text-gray-600 mb-20">Mengetahui,<br>Admin Keuangan</p>
            <p class="text-sm font-bold border-b border-gray-400 pb-1">{{ auth()->user()->name ?? 'Administrator' }}</p>
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.onload = function() {
            // Optional: Uncomment to auto-trigger print dialog when opened
            // window.print();
        }
    </script>
</body>
</html>
