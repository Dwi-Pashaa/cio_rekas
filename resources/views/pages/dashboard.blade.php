@extends('layouts.app')

@section('title', 'Dashboard')
@section('pretitle', 'Ringkasan & Analitik')

@section('header-actions')
    <div class="d-none d-md-flex align-items-center gap-2 px-3 bg-slate-50 border border-slate-200 rounded-2 text-xs font-semibold text-slate-700" style="height: 38px;">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span id="liveClock" class="font-mono text-slate-800">00:00:00 WIB</span>
    </div>
@endsection

@section('content')
<div class="space-y-4">
    {{-- 1. Hero Action Banner (Rich Gradient with Depth & Glow) --}}
    <div class="rounded-4 p-4 p-md-5 text-white shadow-md position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 35%, #2563EB 75%, #4F46E5 100%);">
        
        {{-- Decorative Background Mesh Rings --}}
        <div class="position-absolute" style="top: -60px; right: -50px; width: 280px; height: 280px; border-radius: 50%; background: radial-gradient(circle, rgba(96, 165, 250, 0.25) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>
        <div class="position-absolute" style="bottom: -80px; left: 30%; width: 240px; height: 240px; border-radius: 50%; background: radial-gradient(circle, rgba(168, 85, 247, 0.2) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>

        <div class="position-relative z-1 d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-4">
            <div class="space-y-2">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" 
                     style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2);">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs fw-bold text-white tracking-wide">Sistem POS Online</span>
                    <span class="text-white-50">&bull;</span>
                    <span class="text-xs text-blue-200 fw-medium">{{ Auth::user()->branch->name ?? 'Pusat & Seluruh Cabang' }}</span>
                </div>
                
                <h1 class="text-white fw-extrabold tracking-tight fs-2 mb-1">
                    Selamat Datang, {{ Auth::user()->name }}! 👋
                </h1>
                
                <p class="text-blue-100 fs-6 max-w-2xl m-0 opacity-90">
                    Pantau kinerja penjualan harian, tren omset transaksi, dan stok barang secara realtime dari panel kontrol ini.
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-3 shrink-0">
                <div class="p-3 rounded-3 text-center" style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15); min-width: 140px;">
                    <div class="text-uppercase small fw-bold" style="color: #93C5FD; font-size: 0.7rem; letter-spacing: 0.08em;">OMSET HARI INI</div>
                    <div class="fw-extrabold fs-5 text-white mt-0.5">Rp {{ number_format($kpiMetrics['today_income'], 0, ',', '.') }}</div>
                </div>

                @can('tambah transaksi')
                    <a href="{{ route('transaksi.create') }}" 
                       class="btn btn-light btn-lg px-4 py-3 rounded-3 fw-bold text-blue-800 d-inline-flex align-items-center gap-2 shadow-sm"
                       style="background: #FFFFFF; color: #1E40AF !important; font-size: 0.95rem;">
                        <x-icons.cart class="w-5 h-5 text-blue-700" />
                        <span>Transaksi Baru</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>

    {{-- 2. Four Vibrant Dynamic KPI Cards (Rich Colors with Hover Elevation) --}}
    <div class="row g-3">
        {{-- Card 1: Penjualan Hari Ini (Electric Royal Blue) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white overflow-hidden shadow-sm h-100 position-relative kpi-card" 
                 style="background: linear-gradient(135deg, #1E40AF 0%, #2563EB 50%, #3B82F6 100%);">
                <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.1em; color: #BFDBFE;">
                                OMSET HARI INI
                            </span>
                            <h2 class="fs-3 fw-extrabold text-white mt-1 mb-0">
                                Rp {{ number_format($kpiMetrics['today_income'], 0, ',', '.') }}
                            </h2>
                        </div>
                        <div class="w-11 h-11 rounded-3 d-flex align-items-center justify-center shrink-0 shadow-sm" 
                             style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
                            <x-icons.cash class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.2) !important;">
                        <span class="small text-white-50" style="font-size: 0.78rem;">
                            <b class="text-white">{{ $kpiMetrics['today_transactions_count'] }}</b> transaksi sukses
                        </span>
                        <span class="badge rounded-pill bg-white text-blue-800 fw-bold px-2 py-0.5 text-xs shadow-xs">
                            Hari Ini
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Omset Bulan Ini (Vibrant Emerald Mint) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white overflow-hidden shadow-sm h-100 position-relative kpi-card" 
                 style="background: linear-gradient(135deg, #065F46 0%, #059669 50%, #10B981 100%);">
                <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.1em; color: #A7F3D0;">
                                OMSET BULAN INI
                            </span>
                            <h2 class="fs-3 fw-extrabold text-white mt-1 mb-0">
                                Rp {{ number_format($kpiMetrics['month_income'], 0, ',', '.') }}
                            </h2>
                        </div>
                        <div class="w-11 h-11 rounded-3 d-flex align-items-center justify-center shrink-0 shadow-sm" 
                             style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
                            <x-icons.trending-up class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.2) !important;">
                        <span class="small text-white-50" style="font-size: 0.78rem;">
                            Bulan <b>{{ date('F Y') }}</b>
                        </span>
                        <span class="badge rounded-pill bg-white text-emerald-800 fw-bold px-2 py-0.5 text-xs shadow-xs">
                            {{ $kpiMetrics['month_transactions_count'] }} Transaksi
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Transaksi (Royal Purple Indigo) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white overflow-hidden shadow-sm h-100 position-relative kpi-card" 
                 style="background: linear-gradient(135deg, #4C1D95 0%, #6D28D9 50%, #8B5CF6 100%);">
                <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.1em; color: #DDD6FE;">
                                TOTAL TRANSAKSI
                            </span>
                            <h2 class="fs-3 fw-extrabold text-white mt-1 mb-0">
                                {{ number_format($kpiMetrics['total_transactions_count'], 0, ',', '.') }}
                            </h2>
                        </div>
                        <div class="w-11 h-11 rounded-3 d-flex align-items-center justify-center shrink-0 shadow-sm" 
                             style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
                            <x-icons.receipt class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.2) !important;">
                        <span class="small text-white-50" style="font-size: 0.78rem;">
                            Akumulasi seumur hidup
                        </span>
                        <span class="badge rounded-pill bg-white text-purple-800 fw-bold px-2 py-0.5 text-xs shadow-xs">
                            Lifetime
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Sisa Stok Barang (Sunset Amber Orange) --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 rounded-4 text-white overflow-hidden shadow-sm h-100 position-relative kpi-card" 
                 style="background: linear-gradient(135deg, #9A3412 0%, #EA580C 50%, #F59E0B 100%);">
                <div class="card-body p-4 d-flex flex-column justify-content-between position-relative z-1">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <span class="text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.1em; color: #FED7AA;">
                                STOK UNIT CABANG
                            </span>
                            <h2 class="fs-3 fw-extrabold text-white mt-1 mb-0">
                                {{ number_format($kpiMetrics['total_stock'], 0, ',', '.') }} Unit
                            </h2>
                        </div>
                        <div class="w-11 h-11 rounded-3 d-flex align-items-center justify-center shrink-0 shadow-sm" 
                             style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(4px);">
                            <x-icons.package class="w-6 h-6 text-white" />
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.2) !important;">
                        <span class="small text-white-50" style="font-size: 0.78rem;">
                            @if($kpiMetrics['low_stock_count'] > 0)
                                <b class="text-warning-subtle">{{ $kpiMetrics['low_stock_count'] }} item</b> menipis
                            @else
                                <b class="text-white">Semua stok aman</b>
                            @endif
                        </span>
                        <span class="badge rounded-pill bg-white text-amber-900 fw-bold px-2 py-0.5 text-xs shadow-xs">
                            Inventory
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Visual Charts & Top Selling Products Section --}}
    <div class="row g-3">
        {{-- Revenue Trend Chart (8 Cols on LG) --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden" style="border-top: 4px solid #2563EB !important;">
                <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                            <x-icons.trending-up class="w-5 h-5 text-blue-600" />
                            Tren Pendapatan Bulanan ({{ date('Y') }})
                        </h5>
                        <p class="text-muted small m-0 mt-0.5">Statistik grafik pergerakan omset kasir dalam setahun berjalan</p>
                    </div>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill text-xs fw-bold">
                        Tahunan
                    </span>
                </div>

                <div class="card-body p-4">
                    <div id="chart-revenue-trend" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>

        {{-- Top Selling Products (4 Cols on LG) --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden d-flex flex-column justify-content-between" style="border-top: 4px solid #10B981 !important;">
                <div>
                    <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                        <div>
                            <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                <x-icons.package class="w-5 h-5 text-emerald-600" />
                                Produk Terlaris
                            </h5>
                            <p class="text-muted small m-0 mt-0.5">Barang dengan frekuensi penjualan tertinggi</p>
                        </div>
                        <a href="{{ route('produk.index') }}" class="text-xs fw-bold text-blue-600 text-decoration-none">
                            Katalog &rarr;
                        </a>
                    </div>

                    <div class="card-body p-3 p-md-4">
                        @if($topProducts->count() > 0)
                            <div class="space-y-3">
                                @foreach($topProducts as $item)
                                    @php
                                        // Rank medals styling
                                        $medalBg = 'bg-slate-100 text-slate-700 border border-slate-200';
                                        if ($loop->iteration == 1) {
                                            $medalBg = 'bg-amber-400 text-slate-900 border border-amber-300 font-extrabold shadow-xs';
                                        } elseif ($loop->iteration == 2) {
                                            $medalBg = 'bg-slate-200 text-slate-800 border border-slate-300 font-bold';
                                        } elseif ($loop->iteration == 3) {
                                            $medalBg = 'bg-orange-200 text-orange-900 border border-orange-300 font-bold';
                                        }
                                    @endphp
                                    <div class="p-3 rounded-3 bg-light border border-slate-100 d-flex align-items-center justify-content-between gap-3 hover-scale">
                                        <div class="w-8 h-8 rounded-2 {{ $medalBg }} d-flex align-items-center justify-center shrink-0 fs-6">
                                            {{ $loop->iteration }}
                                        </div>
                                        <div class="overflow-hidden flex-fill">
                                            <div class="fw-bold text-dark text-truncate fs-6">{{ $item->name }}</div>
                                            <div class="text-muted small">Kode: {{ $item->code }}</div>
                                        </div>
                                        <div class="text-end shrink-0">
                                            <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-200 fw-bold px-2 py-1 rounded-pill">
                                                {{ $item->total_sold }} Unit
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-5 text-center text-muted">
                                <x-icons.package class="w-10 h-10 mx-auto mb-2 text-muted opacity-50" />
                                <div class="small fw-semibold">Belum ada data penjualan produk.</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-3 bg-light border-top">
                    <a href="{{ route('transaksi.create') }}" class="btn btn-primary w-100 rounded-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <x-icons.cart class="w-4 h-4" />
                        <span>Input Penjualan Sekarang</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Recent Transactions Feed & Colorful Shortcuts Section --}}
    <div class="row g-3">
        {{-- Recent Transactions Feed (8 Cols on LG) --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden" style="border-top: 4px solid #8B5CF6 !important;">
                <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                            <x-icons.receipt class="w-5 h-5 text-purple-600" />
                            Aktivitas Transaksi Terbaru
                        </h5>
                        <p class="text-muted small m-0 mt-0.5">5 catatan transaksi realtime dari mesin kasir</p>
                    </div>
                    <a href="{{ route('transaksi.index') }}" class="text-xs fw-bold text-purple-600 text-decoration-none">
                        Semua Riwayat &rarr;
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-vcenter card-table align-middle text-nowrap mb-0">
                        <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                            <tr>
                                <th class="py-3 px-4">Agent</th>
                                <th class="py-3">Barang / Paket</th>
                                <th class="py-3">Waktu</th>
                                <th class="py-3 text-end">Total Bayar</th>
                                <th class="py-3 text-center w-24">Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($recentTransactions as $trx)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="w-8 h-8 rounded-circle bg-purple-100 text-purple-700 fw-bold d-flex align-items-center justify-center me-2.5 shrink-0 text-xs">
                                                {{ strtoupper(substr($trx->customer->name ?? 'A', 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6">{{ $trx->customer->name ?? 'Agent Umum' }}</div>
                                                <div class="text-muted small font-monospace">SN: {{ $trx->customer->code ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $trx->product->name ?? '-' }}</div>
                                        <div class="text-muted small">{{ $trx->qty }} unit</div>
                                    </td>
                                    <td class="text-muted small">
                                        {{ $trx->created_at ? $trx->created_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="text-end fw-extrabold text-dark fs-6">
                                        Rp {{ number_format($trx->total, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('transaksi.show', $trx->id) }}" target="_blank" 
                                           class="btn btn-sm btn-outline-primary px-2.5 py-1 rounded-2 d-inline-flex align-items-center gap-1"
                                           title="Cetak Struk">
                                            <x-icons.printer class="w-3.5 h-3.5" />
                                            <span class="text-xs">Struk</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <x-icons.receipt class="w-10 h-10 mx-auto mb-2 text-muted opacity-50" />
                                        <div class="small fw-semibold">Belum ada transaksi tercatat.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Navigation Actions (4 Cols on LG) --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm overflow-hidden h-100">
                <div class="card-header bg-white py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                    <div>
                        <h5 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                            <x-icons.dashboard class="w-5 h-5 text-blue-600" />
                            Pintasan Cepat
                        </h5>
                        <p class="text-muted small m-0 mt-0.5">Akses modul operasional sekali klik</p>
                    </div>
                    <span class="badge bg-slate-100 text-slate-700 border border-slate-200 px-2.5 py-1 rounded-pill text-xs fw-semibold">
                        {{ Auth::user()->getRoleNames()[0] ?? 'Kasir' }}
                    </span>
                </div>

                <div class="card-body p-3 d-flex flex-column justify-content-around gap-2">
                    {{-- 1. Kasir POS --}}
                    <a href="{{ route('transaksi.create') }}" class="quick-action-item d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 bg-blue-50 text-blue-600 border border-blue-200 d-flex align-items-center justify-center shrink-0" style="width: 42px; height: 42px;">
                                <x-icons.cart class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6 lh-sm">Kasir POS</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Buka transaksi penjualan kasir</div>
                            </div>
                        </div>
                        <x-icons.chevron-right class="w-4 h-4 text-slate-400 chevron-icon" />
                    </a>

                    {{-- 2. Stok Barang --}}
                    <a href="{{ route('produk.index') }}" class="quick-action-item d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 bg-emerald-50 text-emerald-600 border border-emerald-200 d-flex align-items-center justify-center shrink-0" style="width: 42px; height: 42px;">
                                <x-icons.package class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6 lh-sm">Katalog Produk</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Kelola barang & cek sisa stok</div>
                            </div>
                        </div>
                        <x-icons.chevron-right class="w-4 h-4 text-slate-400 chevron-icon" />
                    </a>

                    {{-- 3. Agent & Member --}}
                    <a href="{{ route('customer.index') }}" class="quick-action-item d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 bg-purple-50 text-purple-600 border border-purple-200 d-flex align-items-center justify-center shrink-0" style="width: 42px; height: 42px;">
                                <x-icons.users class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6 lh-sm">Data Agent</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Member card & kuota transaksi</div>
                            </div>
                        </div>
                        <x-icons.chevron-right class="w-4 h-4 text-slate-400 chevron-icon" />
                    </a>

                    {{-- 4. Keuangan / Rekap --}}
                    <a href="{{ route('keuangan.index') }}" class="quick-action-item d-flex align-items-center justify-content-between p-2.5 rounded-3 text-decoration-none">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-2 bg-amber-50 text-amber-600 border border-amber-200 d-flex align-items-center justify-center shrink-0" style="width: 42px; height: 42px;">
                                <x-icons.finance class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6 lh-sm">Laporan Keuangan</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">Rekap kas masuk & laba rugi</div>
                            </div>
                        </div>
                        <x-icons.chevron-right class="w-4 h-4 text-slate-400 chevron-icon" />
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Vibrant KPI Card Hover Animations */
    .kpi-card {
        transition: transform 0.22s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 30px -6px rgba(15, 23, 42, 0.22) !important;
    }

    /* Quick Action Item Hover */
    .quick-action-item {
        border: 1px solid transparent;
        background-color: #FFFFFF;
        transition: all 0.18s ease-in-out;
    }
    .quick-action-item:hover {
        background-color: #F8FAFC;
        border-color: #E2E8F0;
        transform: translateX(4px);
    }
    .quick-action-item:hover .chevron-icon {
        color: #2563EB !important;
        transform: translateX(3px);
        transition: all 0.18s ease;
    }

    .hover-scale {
        transition: transform 0.15s ease;
    }
    .hover-scale:hover {
        transform: translateX(3px);
    }
</style>
@endpush

@push('js')
<script>
    // Live Clock Function
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const clockEl = document.getElementById('liveClock');
        if (clockEl) {
            clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Render Revenue Trend Chart with ApexCharts
    document.addEventListener('DOMContentLoaded', function() {
        const trendData = {!! json_encode($monthlyTrend ?? []) !!};
        const months = {!! json_encode($months ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!};

        const options = {
            chart: {
                type: 'area',
                height: 320,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3,
                colors: ['#2563EB']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.5,
                    opacityTo: 0.05,
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: '#2563EB', opacity: 0.45 },
                        { offset: 100, color: '#2563EB', opacity: 0.0 }
                    ]
                }
            },
            series: [{
                name: 'Total Omset',
                data: trendData
            }],
            xaxis: {
                categories: months,
                labels: {
                    style: { colors: '#64748B', fontSize: '11px', fontWeight: 600 }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748B', fontSize: '11px', fontWeight: 500 },
                    formatter: function (value) {
                        return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                    }
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (value) {
                        return 'Rp ' + Number(value).toLocaleString('id-ID');
                    }
                }
            },
            grid: {
                borderColor: '#F1F5F9',
                strokeDashArray: 4,
            },
            colors: ['#2563EB'],
            markers: {
                size: 4,
                colors: ['#2563EB'],
                strokeColors: '#fff',
                strokeWidth: 2,
                hover: { size: 6 }
            }
        };

        if (window.ApexCharts) {
            const chart = new ApexCharts(document.querySelector("#chart-revenue-trend"), options);
            chart.render();
        }
    });
</script>
@endpush