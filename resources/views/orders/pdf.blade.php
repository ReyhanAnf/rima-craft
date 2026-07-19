<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
        }
        table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        table td {
            padding: 8px;
            vertical-align: top;
        }
        .header-table td {
            padding: 0;
            padding-bottom: 20px;
        }
        .title {
            font-size: 28px;
            line-height: 32px;
            color: #d97706; /* amber-600 */
            font-weight: bold;
            text-transform: uppercase;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
        }
        .info-table {
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .info-table td {
            padding: 0;
        }
        .details-table {
            width: 100%;
            margin-bottom: 30px;
        }
        .details-table th {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            font-weight: bold;
            padding: 10px;
            font-size: 12px;
            text-align: left;
        }
        .details-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
        }
        .total-box {
            float: right;
            width: 300px;
            margin-bottom: 30px;
        }
        .total-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #f3f4f6;
        }
        .grand-total {
            font-weight: bold;
            font-size: 15px;
            border-bottom: 2px solid #333 !important;
            background: #f9fafb;
        }
        .footer-signatures {
            margin-top: 50px;
            clear: both;
        }
        .signature-box {
            width: 45%;
            float: left;
            text-align: center;
        }
        .signature-box-right {
            width: 45%;
            float: right;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td>
                    <span class="title">{{ config('settings.business_name', 'Rima Craft') }}</span><br>
                    <span style="color: #666; font-size: 11px;">
                        Pengrajin furnitur dan dekorasi rotan premium<br>
                        {{ config('settings.address', 'Indonesia') }}
                    </span>
                </td>
                <td class="invoice-title">
                    INVOICE PESANAN<br>
                    <span style="font-size: 12px; color: #666; font-weight: normal;">
                        #{{ $order->order_number }}
                    </span>
                </td>
            </tr>
        </table>

        <!-- Customer & Invoice Meta -->
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <strong style="color: #666; font-size: 11px; text-transform: uppercase;">Kepada Yth.</strong><br>
                    <span style="font-size: 14px; font-weight: bold; color: #111;">{{ $order->customer_name }}</span><br>
                    @if($order->customer_phone)
                        <span>Telp: {{ $order->customer_phone }}</span><br>
                    @endif
                    @if($order->customer_email)
                        <span>Email: {{ $order->customer_email }}</span><br>
                    @endif
                    @if($order->customer_address)
                        <span style="font-size: 11px; color: #555;">Alamat: {{ $order->customer_address }}</span>
                    @endif
                </td>
                <td style="width: 50%; text-align: right; font-size: 12px;">
                    <strong>Tanggal Pesanan:</strong> {{ $order->created_at->translatedFormat('d F Y') }}<br>
                    <strong>Status Pesanan:</strong> <span style="text-transform: uppercase; font-weight: bold;">{{ $order->status }}</span><br>
                    <strong>Status Pembayaran:</strong> <span style="text-transform: uppercase; font-weight: bold; color: {{ $order->payment_status === 'paid' ? '#10b981' : ($order->payment_status === 'partial' ? '#f59e0b' : '#ef4444') }}">{{ $order->payment_status }}</span><br>
                    <strong>Metode Pembayaran:</strong> <span style="text-transform: uppercase; font-weight: bold;">{{ $order->payment_method }} ({{ $order->order_method }})</span>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 8%; text-align: center;">No</th>
                    <th>Nama Produk</th>
                    <th style="width: 12%; text-align: center;">Jumlah</th>
                    <th style="width: 20%; text-align: right;">Harga Satuan</th>
                    <th style="width: 22%; text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ is_array($item) ? ($item['name'] ?? 'Produk') : ($item->name ?? 'Produk') }}</strong>
                        </td>
                        <td class="text-center">{{ is_array($item) ? ($item['qty'] ?? 1) : ($item->qty ?? 1) }}</td>
                        <td class="text-right">Rp {{ number_format(is_array($item) ? ($item['price'] ?? 0) : ($item->price ?? 0), 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((is_array($item) ? ($item['qty'] ?? 1) : ($item->qty ?? 1)) * (is_array($item) ? ($item['price'] ?? 0) : ($item->price ?? 0)), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="total-box">
            <table class="total-table">
                <tr>
                    <td style="color: #666;">Subtotal:</td>
                    <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($order->shipping_cost > 0)
                    <tr>
                        <td style="color: #666;">Biaya Pengiriman:</td>
                        <td class="text-right">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="grand-total">
                    <td>Grand Total:</td>
                    <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Signatures -->
        <div class="footer-signatures">
            <div class="signature-box">
                <p style="color: #666; margin-bottom: 60px;">Penerima / Pelanggan,</p>
                <p style="font-weight: bold; text-decoration: underline;">{{ $order->customer_name }}</p>
            </div>
            <div class="signature-box-right">
                <p style="color: #666; margin-bottom: 60px;">Hormat Kami,</p>
                <p style="font-weight: bold; text-decoration: underline;">{{ config('settings.business_name', 'Rima Craft') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
