<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaction->id }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            width: 58mm;
            margin: 0 auto;
            padding: 8px 4px 20px 4px;
            box-sizing: border-box;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .btn-print {
            display: inline-block;
            margin-top: 15px;
            padding: 6px 14px;
            background: #2563EB;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: sans-serif;
            font-size: 12px;
            cursor: pointer;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                width: 100%;
                padding: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div style="font-size: 13px; font-weight: bold;">{{ $usaha->name ?? config('app.name') }}</div>
        <div style="font-size: 9.5px; margin-top: 2px;">{{ $usaha->address ?? '' }}</div>
        @if($usaha && $usaha->telp)
            <div style="font-size: 9.5px;">Telp: {{ $usaha->telp }}</div>
        @endif
    </div>

    <div class="line"></div>

    <div style="font-size: 10px;">
        <table>
            <tr>
                <td style="width: 45%;">No. Nota</td>
                <td>: #{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: {{ date('d/m/y H:i', strtotime($transaction->created_at)) }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $transaction->customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>SN</td>
                <td>: {{ $transaction->customer->code ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ optional($transaction->casier)->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    <div style="font-size: 10px;">
        <div style="font-weight: bold;">{{ $transaction->product->name ?? 'Barang' }}</div>
        <table>
            <tr>
                <td>{{ $transaction->qty }} x Rp {{ number_format($transaction->product->selling_price ?? 0, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="line"></div>

    <table>
        <tr class="fw-bold">
            <td>Total</td>
            <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="text-right">Rp {{ number_format($transaction->payment, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">Rp {{ number_format(max(0, $transaction->payment - $transaction->total), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="font-size: 9.5px; margin-top: 8px;">
        <div>{{ $usaha->footer ?? 'Terima Kasih Atas Kunjungan Anda' }}</div>
        <div style="font-size: 8.5px; margin-top: 4px; color: #555;">Struk Resmi &bull; CIO REKAS POS</div>
    </div>

    <div class="text-center no-print">
        <button onclick="window.print();" class="btn-print">
            Cetak Struk
        </button>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
