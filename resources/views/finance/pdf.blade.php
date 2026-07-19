<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Arus Kas - {{ config('settings.business_name', 'Rima Craft') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 12px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 2px 0; border: none !important; }
        .summary-box { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-box td { border: 1px solid #ddd; padding: 10px; background-color: #f9fafb; width: 33.33%; }
        .summary-title { font-size: 10px; text-transform: uppercase; font-weight: bold; color: #666; margin-bottom: 5px; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .summary-value.income { color: #0f766e; }
        .summary-value.expense { color: #b91c1c; }
        .ledger-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .ledger-table th { background-color: #f3f4f6; font-weight: bold; text-transform: uppercase; font-size: 10px; border: 1px solid #ddd; padding: 8px; text-align: left; }
        .ledger-table td { border: 1px solid #ddd; padding: 6px 8px; font-size: 11px; }
        .text-right { text-align: right; }
        .text-emerald { color: #16a34a; font-weight: 500; }
        .text-red { color: #dc2626; font-weight: 500; }
        .signature-container { float: right; width: 200px; text-align: center; margin-top: 30px; }
        .signature-space { height: 60px; }
        .signature-name { font-weight: bold; border-bottom: 1px solid #333; padding-bottom: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ config('settings.business_name', 'Rima Craft') }}</h1>
        <p>Laporan Keuangan - Arus Kas (Cash Flow)</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">Periode</td>
            <td>: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</td>
            <td style="text-align: right; color: #666; font-size: 10px;">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Rekening Kas</td>
            <td colspan="2">: {{ $account ? $account->name : 'Semua Rekening' }}</td>
        </tr>
    </table>

    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Pemasukan</div>
                <div class="summary-value income">Rp {{ number_format($totalIncome, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-title">Total Pengeluaran</div>
                <div class="summary-value expense">Rp {{ number_format($totalExpense, 0, ',', '.') }}</div>
            </td>
            <td style="background-color: #f3f4f6;">
                <div class="summary-title">Arus Kas Bersih (Net)</div>
                <div class="summary-value" style="font-size: 14px;">Rp {{ number_format($netCashFlow, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 15%;">Rekening</th>
                <th>Keterangan</th>
                <th class="text-right" style="width: 15%;">Masuk (Rp)</th>
                <th class="text-right" style="width: 15%;">Keluar (Rp)</th>
                <th class="text-right" style="width: 15%;">Saldo Akhir (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ledgers as $index => $ledger)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $ledger->date->format('d/m/Y') }}</td>
                    <td>{{ $ledger->account->name }}</td>
                    <td>{{ $ledger->description }}</td>
                    <td class="text-right text-emerald">
                        {{ $ledger->type === 'in' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right text-red">
                        {{ $ledger->type === 'out' ? number_format($ledger->amount, 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right" style="font-weight: bold;">
                        {{ number_format($ledger->balance_after, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 15px; color: #666; font-style: italic;">Tidak ada transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="signature-container">
        <p style="margin: 0;">Mengetahui,<br>Admin Keuangan</p>
        <div class="signature-space"></div>
        <p class="signature-name">{{ auth()->user()->name ?? 'Administrator' }}</p>
    </div>

</body>
</html>
