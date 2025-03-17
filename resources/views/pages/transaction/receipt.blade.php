<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi Pembelian</title>
    <style>
        @page {
            size: 48mm auto; /* Ukuran kertas 48mm */
            margin: 5mm; /* Beri sedikit margin */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 48mm;
            padding: 10px 5px; /* Tambahkan padding atas & bawah */
            text-align: center;
        }
        .logo {
            max-width: 80px; /* Sesuaikan ukuran logo */
            margin-bottom: 5px;
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
            margin-top: 10px;
            font-size: 10px;
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
                padding: 10px 5px; /* Pastikan tetap ada padding saat cetak */
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    {{-- <img src="{{ asset($usaha->image) }}" alt="Logo Toko" class="logo"> --}}
    <p style="font-size: 14px"><strong>{{ $usaha->name }}</strong></p>
    <p style="font-size: 10px">{{ $usaha->address }}</p>
    <div class="line"></div>
    
    <div class="info">
        <p><strong>Tanggal:</strong> {{ date('d-m-Y H:i', strtotime($transaction->created_at)) }}</p>
        <p><strong>Pelanggan:</strong> {{ $transaction->customer->name }}</p>
        <p><strong>Alamat:</strong> {{ $transaction->customer->address }}</p>
    </div>

    <div class="line"></div>
    <div class="items">
        <p>{{ $transaction->product->name }} x {{ $transaction->qty }}
        <span style="float: right;">Rp{{ number_format($transaction->product->selling_price) }}</span></p>
    </div>
    <div class="line"></div>

    <!-- Menggunakan tabel agar teks kiri & angka kanan tetap rapi -->
    <table>
        <tr>
            <td class="left"><strong>Total</strong></td>
            <td class="right">Rp{{ number_format($transaction->total) }}</td>
        </tr>
        <tr>
            <td class="left">Pembayaran</td>
            <td class="right">Rp{{ number_format($transaction->payment) }}</td>
        </tr>
        <tr>
            <td class="left">Kembalian</td>
            <td class="right">Rp{{ number_format($transaction->payment - $transaction->total) }}</td>
        </tr>
    </table>

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
