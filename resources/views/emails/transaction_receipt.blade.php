<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi Pembelian</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
            line-height: 1.5;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
            color: #ffffff;
            padding: 32px 28px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #bfdbfe;
        }
        .badge-success {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            padding: 4px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .body {
            padding: 28px;
        }
        .greeting {
            font-size: 15px;
            margin-bottom: 20px;
            color: #334155;
        }
        .info-grid {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .table-items th {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .table-items td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .total-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        .total-highlight {
            font-size: 18px;
            font-weight: 800;
            color: #1d4ed8;
            padding-top: 8px;
            border-top: 1px solid #bfdbfe;
            margin-top: 6px;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 28px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>{{ $usaha->name ?? config('app.name', 'CIO REKAS') }}</h1>
            <p>{{ $usaha->address ?? 'Layanan Point of Sale Resmi' }}</p>
            <div class="badge-success">&#10003; Transaksi Berhasil</div>
        </div>

        {{-- Body --}}
        <div class="body">
            <div class="greeting">
                Halo <b>{{ $transaction->customer->name ?? 'Pelanggan' }}</b>,<br>
                Terima kasih telah melakukan transaksi di {{ $usaha->name ?? config('app.name', 'CIO REKAS') }}. Berikut adalah rincian struk pembelian Anda:
            </div>

            {{-- Metadata Grid --}}
            <div class="info-grid">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">No. Transaksi:</td>
                        <td class="info-val" style="padding: 4px 0; text-align: right;">#TRX-{{ str_pad($transaction->id, 6, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Serial Number Agent:</td>
                        <td class="info-val" style="padding: 4px 0; text-align: right; color: #2563eb;">{{ $transaction->customer->code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Tanggal & Waktu:</td>
                        <td class="info-val" style="padding: 4px 0; text-align: right;">{{ \Carbon\Carbon::parse($transaction->created_at)->translatedFormat('d F Y, H:i') }} WIB</td>
                    </tr>
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Kasir Bertugas:</td>
                        <td class="info-val" style="padding: 4px 0; text-align: right;">{{ $transaction->casier->name ?? 'Kasir' }}</td>
                    </tr>
                    @if($transaction->branch)
                    <tr>
                        <td class="info-label" style="padding: 4px 0;">Cabang:</td>
                        <td class="info-val" style="padding: 4px 0; text-align: right;">{{ $transaction->branch->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            {{-- Table Items --}}
            <table class="table-items">
                <thead>
                    <tr>
                        <th>Paket / Barang</th>
                        <th style="text-align: center;">Jumlah</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <b>{{ $transaction->product->name ?? 'Paket Transaksi' }}</b><br>
                            <span style="font-size: 11px; color: #64748b;">Kode: {{ $transaction->product->code ?? '-' }}</span>
                        </td>
                        <td style="text-align: center;">{{ $transaction->qty ?? 1 }} Unit</td>
                        <td style="text-align: right;">Rp {{ number_format($transaction->product->selling_price ?? ($transaction->total / ($transaction->qty ?: 1)), 0, ',', '.') }}</td>
                        <td style="text-align: right; font-weight: 700;">Rp {{ number_format($transaction->total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Summary Box --}}
            <div class="total-box">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #475569;">Total Tagihan:</td>
                        <td style="padding: 4px 0; font-size: 13px; text-align: right; font-weight: 700; color: #1e40af;">
                            Rp {{ number_format($transaction->total ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #475569;">Uang Diterima:</td>
                        <td style="padding: 4px 0; font-size: 13px; text-align: right; font-weight: 700; color: #0f172a;">
                            Rp {{ number_format($transaction->payment ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @php
                        $kembalian = max(0, floatval($transaction->payment ?? 0) - floatval($transaction->total ?? 0));
                    @endphp
                    <tr>
                        <td style="padding: 4px 0; font-size: 13px; color: #475569;">Kembalian:</td>
                        <td style="padding: 4px 0; font-size: 13px; text-align: right; font-weight: 700; color: #10b981;">
                            Rp {{ number_format($kembalian, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>
            </div>

            <div style="font-size: 13px; color: #475569; text-align: center; margin-top: 10px;">
                {{ $usaha->footer ?? 'Simpan bukti pembayaran ini sebagai struk resmi transaksi Anda.' }}
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><b>{{ $usaha->name ?? config('app.name', 'CIO REKAS') }}</b></p>
            <p>{{ $usaha->address ?? '' }}</p>
            <p style="margin-top: 8px; font-size: 11px; color: #94a3b8;">Email ini dibuat secara otomatis oleh sistem point of sale resmi.</p>
        </div>
    </div>
</body>
</html>
