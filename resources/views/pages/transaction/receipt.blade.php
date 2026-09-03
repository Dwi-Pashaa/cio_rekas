<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }} - {{ $usaha->name ?? config('app.name') }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace, 'Lucida Console';
            font-size: 11px;
            line-height: 1.35;
            color: #000;
            width: 58mm;
            margin: 0 auto;
            padding: 10px 6px 20px 6px;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-uppercase { text-transform: uppercase; }

        /* Logo Struk */
        .receipt-logo {
            max-height: 42px;
            max-width: 140px;
            object-fit: contain;
            margin-bottom: 6px;
            filter: grayscale(100%) contrast(150%);
        }

        /* Garis Pemisah Thermal */
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
            width: 100%;
        }
        .divider-double {
            border-top: 1px double #000;
            margin: 6px 0;
            width: 100%;
        }

        /* Tabel Rincian */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 10.5px;
        }

        .item-row td {
            padding-top: 3px;
        }
        .item-detail td {
            font-size: 10px;
            color: #111;
        }

        /* Summary Total */
        .total-table td {
            padding: 2px 0;
            font-size: 10.5px;
        }
        .total-row {
            font-size: 12px;
            font-weight: bold;
        }

        /* Tombol Aksi Layar (Tidak Tercetak) */
        .action-bar {
            margin-top: 20px;
            padding-top: 10px;
            text-align: center;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            background: #2563EB;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
            text-decoration: none;
        }
        .btn-print:hover {
            background: #1D4ED8;
        }
        .btn-back {
            display: inline-block;
            margin-top: 8px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #64748B;
            text-decoration: none;
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                width: 100%;
                margin: 0;
                padding: 4px 2px;
            }
        }
    </style>
</head>
<body>

    {{-- HEADER TOKO & LOGO --}}
    <div class="text-center">
        @if(!empty($usaha->image) && file_exists(public_path($usaha->image)))
            <div>
                <img src="{{ asset($usaha->image) }}" alt="Logo" class="receipt-logo">
            </div>
        @endif

        <div class="fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
            {{ $usaha->name ?? config('app.name', 'CIO REKAS') }}
        </div>
        
        @if(!empty($usaha->address))
            <div style="font-size: 9.5px; margin-top: 2px;">
                {{ $usaha->address }}
            </div>
        @endif

        @if($transaction->branch)
            <div style="font-size: 9.5px;">
                Cabang: {{ $transaction->branch->name }}
            </div>
        @endif

        @if(!empty($usaha->telp))
            <div style="font-size: 9px;">
                Telp: {{ $usaha->telp }}
            </div>
        @endif
    </div>

    <div class="divider"></div>

    {{-- METADATA TRANSAKSI --}}
    <table>
        <tr>
            <td style="width: 32%;">No. Nota</td>
            <td style="width: 4%;">:</td>
            <td class="fw-bold">#TRX-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d/m/y H:i') }} WIB</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td>{{ optional($transaction->casier)->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>:</td>
            <td class="fw-bold">{{ $transaction->customer->name ?? '-' }}</td>
        </tr>
        <tr>
            <td>SN Agent</td>
            <td>:</td>
            <td class="fw-bold">{{ $transaction->customer->code ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- RINCIAN BARANG / PRODUK --}}
    <table>
        <tr class="item-row">
            <td colspan="2" class="fw-bold">
                {{ $transaction->product->name ?? 'Paket Transaksi' }}
            </td>
        </tr>
        @php
            $unitPrice = $transaction->product->selling_price ?? ($transaction->qty > 0 ? ($transaction->total / $transaction->qty) : $transaction->total);
            $kembalian = max(0, floatval($transaction->payment ?? 0) - floatval($transaction->total ?? 0));
        @endphp
        <tr class="item-detail">
            <td style="width: 55%;">
                {{ $transaction->qty }} x Rp {{ number_format($unitPrice, 0, ',', '.') }}
            </td>
            <td class="text-right fw-bold">
                Rp {{ number_format($transaction->total, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- SUMMARY TOTAL PEMBAYARAN --}}
    <table class="total-table">
        <tr class="total-row">
            <td class="text-uppercase">TOTAL</td>
            <td class="text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>BAYAR (TUNAI)</td>
            <td class="text-right">Rp {{ number_format($transaction->payment, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>KEMBALI</td>
            <td class="text-right fw-bold">Rp {{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider-double"></div>

    {{-- FOOTER STRUK --}}
    <div class="text-center" style="margin-top: 6px;">
        <div style="font-size: 9.5px; font-weight: bold;">
            {{ $usaha->footer ?? 'TERIMA KASIH ATAS KUNJUNGAN ANDA' }}
        </div>
        <div style="font-size: 8.5px; margin-top: 3px; color: #333;">
            *** STRUK PEMBAYARAN SAH ***
        </div>
    </div>

    {{-- TOMBOL CETAK & AKSI (DI LAYAR SAJA) --}}
    <div class="action-bar no-print">
        <button onclick="window.print();" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            <span>Cetak Ulang Struk</span>
        </button>
        <div>
            <a href="javascript:window.history.back();" class="btn-back">&larr; Kembali ke Aplikasi</a>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
