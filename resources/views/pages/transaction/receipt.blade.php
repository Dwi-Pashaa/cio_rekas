<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi Pembelian</title>
    <style>
        @page {
            size: 58mm auto; /* Ukuran kertas 58mm */
            margin: 0; /* Hilangkan margin agar sesuai */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 58mm;
            padding: 5px;
            text-align: center;
        }
        .logo {
            max-width: 100px;
            margin-bottom: 5px;
        }
        .line {
            border-top: 1px dashed black;
            margin: 5px 0;
        }
        .items {
            text-align: left;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 10px;
            font-size: 10px;
        }
        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <img src="{{ asset($usaha->image) }}" alt="Logo Toko" class="logo">
    <p><strong>{{ $usaha->name }}</strong></p>
    <p>{{ $usaha->address }}</p>
    <div class="line"></div>
    <div class="items">
        <p>{{ $transaction->product->name }} x {{ $transaction->qty }}
        <span style="float: right;">Rp{{ number_format($transaction->product->selling_price) }}</span></p>
    </div>
    <div class="line"></div>
    <p class="total">Total: <span style="float: right;">Rp{{ number_format($transaction->total) }}</span></p>
    <p>Pembayaran: <span style="float: right;">Rp{{ number_format($transaction->payment) }}</span></p>
    <p>Kembalian: <span style="float: right;">Rp{{ number_format($transaction->total - $transaction->payment) }}</span></p>
    <div class="line"></div>
    <p class="footer">
        {{ $usaha->footer }}
    </p>

    <button onclick="window.print();" class="no-print">Cetak</button>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
