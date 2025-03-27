<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi Pembelian</title>
    <style>
        @page {
            size: 48mm auto; /* Ukuran kertas 48mm */
            margin-bottom: 20mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 48mm;
            padding: 10px 5px;
            text-align: center;
            display: flex;
            flex-direction: column;
            /* min-height: 100mm; Memberikan tinggi minimum agar footer tidak mentok */
            justify-content: space-between;
        }
        .line {
            border-top: 1px dashed black;
            margin: 8px 0;
        }
        .info, .items {
            text-align: left;
            margin: 5px 0;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: auto; /* Memastikan footer tetap di bagian bawah */
            font-size: 10px;
            padding-bottom: 15mm; /* Tambahkan padding bawah agar tidak terpotong */
        }
        table {
            width: 100%;
        }
        table td {
            vertical-align: top;
        }
        .left {
            text-align: left;
        }
        .right {
            text-align: right;
        }
        @media print {
            body {
                width: 48mm;
                margin: 0;
                padding: 10px 5px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <p style="font-size: 14px"><strong>{{ $usaha->name }}</strong></p>
    <p style="font-size: 10px">{{ $usaha->address }}</p>
    <div class="line"></div>
    
    <div class="info">
        <p><strong>Tanggal:</strong> {{ date('d-m-Y H:i', strtotime($transaction->created_at)) }}</p>
        <p><strong>Penjual:</strong> {{ $transaction->customer->name }}</p>
        <p><strong>Alamat:</strong> {{ $transaction->customer->address }}</p>
        <p><strong>Status:</strong> {{ optional($transaction->customer->status)->name ?? '-' }}</p>
    </div>

    <div class="line"></div>
    <div class="items">
        <p style="font-size: 11px">{{ $transaction->product->name }} x {{ $transaction->qty }}
        <span style="float: right; font-size: 11px">Rp. {{ number_format($transaction->product->selling_price) }}</span></p>
    </div>
    <div class="line"></div>

    <table>
        <tr>
            <td class="left"><strong>Total</strong></td>
            <td class="right">Rp. {{ number_format($transaction->total) }}</td>
        </tr>
        <tr>
            <td class="left">Pembayaran</td>
            <td class="right">Rp. {{ number_format($transaction->payment) }}</td>
        </tr>
        <tr>
            <td class="left">Kembalian</td>
            <td class="right">Rp. {{ number_format($transaction->payment - $transaction->total) }}</td>
        </tr>
    </table>

    <div class="line"></div>
    <p class="footer">
        {{ $usaha->footer }}
    </p>
    <br>
    <div class="line"></div>
    <br>
    <button onclick="window.print();" class="no-print">Cetak</button>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
