<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }} - {{ config('settings.business_name', 'Rima Craft') }}</title>
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
<body class="p-8 max-w-4xl mx-auto">

    <!-- Print Action Buttons (Hidden in Print) -->
    <div class="no-print flex justify-end mb-8 space-x-4">
        <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-semibold hover:bg-gray-300">Tutup</button>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm font-semibold hover:bg-blue-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Invoice
        </button>
    </div>

    <!-- Header Laporan -->
    <div class="flex justify-between items-start border-b-2 border-gray-900 pb-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold uppercase tracking-widest">{{ config('settings.business_name', 'Rima Craft') }}</h1>
            <p class="text-gray-500 text-sm mt-1 max-w-xs">Pengrajin furnitur dan dekorasi rotan premium dengan kualitas ekspor.</p>
        </div>
        <div class="text-right">
            <h2 class="text-2xl font-black text-gray-900 tracking-wider uppercase">INVOICE</h2>
            <p class="text-gray-600 mt-1 font-semibold">#INV-{{ str_pad($sale->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>

    <!-- Informasi Faktur & Pelanggan -->
    <div class="flex justify-between mb-8">
        <div>
            <div class="text-xs text-gray-500 uppercase font-bold mb-1">Kepada Yth.</div>
            <div class="font-bold text-gray-900 text-lg">{{ $sale->customer ? $sale->customer->name : $sale->customer_name }}</div>
            @if($sale->customer_phone || ($sale->customer && $sale->customer->phone))
                <div class="text-gray-600 text-sm">{{ $sale->customer ? $sale->customer->phone : $sale->customer_phone }}</div>
            @endif
        </div>
        <div class="text-right">
            <table class="text-sm ml-auto">
                <tr>
                    <td class="py-1 font-semibold border-none !p-0 pr-4 text-right">Tanggal Faktur</td>
                    <td class="py-1 border-none !p-0 text-right">: {{ $sale->date->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold border-none !p-0 pr-4 text-right">Status Pembayaran</td>
                    <td class="py-1 border-none !p-0 text-right">: 
                        <span class="uppercase font-bold {{ $sale->payment_status === 'paid' ? 'text-emerald-600' : ($sale->payment_status === 'partial' ? 'text-orange-600' : 'text-red-600') }}">
                            {{ $sale->payment_status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold border-none !p-0 pr-4 text-right">Status Pengiriman</td>
                    <td class="py-1 border-none !p-0 text-right">: <span class="uppercase font-bold">{{ $sale->shipping_status }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Transaksi Table -->
    <table class="w-full text-sm mb-8">
        <thead class="bg-gray-100 border-b-2 border-gray-300">
            <tr>
                <th class="py-3 px-4 text-left font-bold text-gray-700 w-12">No</th>
                <th class="py-3 px-4 text-left font-bold text-gray-700">Nama Produk</th>
                <th class="py-3 px-4 text-center font-bold text-gray-700 w-24">Jumlah</th>
                <th class="py-3 px-4 text-right font-bold text-gray-700 w-32">Harga Satuan</th>
                <th class="py-3 px-4 text-right font-bold text-gray-700 w-40">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $index => $item)
                <tr class="border-b border-gray-200">
                    <td class="py-3 px-4 text-gray-600 text-center">{{ $index + 1 }}</td>
                    <td class="py-3 px-4 text-gray-900 font-medium">{{ $item->product->name }}</td>
                    <td class="py-3 px-4 text-gray-900 text-center">{{ $item->qty }}</td>
                    <td class="py-3 px-4 text-right text-gray-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-right text-gray-900 font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total Section -->
    <div class="flex justify-end mb-12">
        <div class="w-80">
            <div class="flex justify-between py-2 border-b border-gray-200 text-sm">
                <span class="text-gray-600">Total Item:</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($sale->shipping_fee > 0)
            <div class="flex justify-between py-2 border-b border-gray-200 text-sm">
                <span class="text-gray-600">Biaya Pengiriman:</span>
                <span class="font-semibold text-gray-900">Rp {{ number_format($sale->shipping_fee, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($sale->discount > 0)
            <div class="flex justify-between py-2 border-b border-gray-200 text-sm text-emerald-600">
                <span class="font-medium">Diskon:</span>
                <span class="font-bold">- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between py-3 border-b-2 border-gray-900 text-lg mt-2">
                <span class="font-bold text-gray-900">Grand Total:</span>
                <span class="font-black text-gray-900">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between py-2 text-sm mt-2 text-gray-600">
                <span>Sudah Dibayar:</span>
                <span class="font-semibold text-emerald-600">Rp {{ number_format($sale->payments->sum('amount'), 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between py-2 text-sm">
                <span class="font-bold">Sisa Tagihan:</span>
                <span class="font-bold text-red-600">Rp {{ number_format($sale->grand_total - $sale->payments->sum('amount'), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Tanda Tangan Section -->
    <div class="flex justify-between mt-16 text-sm">
        <div class="text-center w-48">
            <p class="text-gray-600 mb-20">Penerima / Pelanggan,</p>
            <p class="font-bold border-b border-gray-400 pb-1">{{ $sale->customer ? $sale->customer->name : $sale->customer_name }}</p>
        </div>
        <div class="text-center w-48">
            <p class="text-gray-600 mb-20">Hormat Kami,</p>
            <p class="font-bold border-b border-gray-400 pb-1">{{ config('settings.business_name', 'Rima Craft') }}</p>
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
