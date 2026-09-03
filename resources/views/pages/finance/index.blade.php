@extends('layouts.app')

@section('title', 'Rekap Keuangan & Laporan Penjualan')
@section('pretitle', 'Keuangan & Akuntansi')

@push('css')
<style>
    /* Styling khusus ApexCharts Tooltip */
    .apexcharts-tooltip {
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 4px 8px !important;
    }
    .apexcharts-tooltip-title {
        background: #f8fafc !important;
        border-bottom: 1px solid #e2e8f0 !important;
        font-weight: 700 !important;
        color: #1e293b !important;
    }
    
    @media (max-width: 575.98px) {
        .kpi-value {
            font-size: 1.35rem !important;
        }
    }
    @media (min-width: 576px) {
        .kpi-value {
            font-size: 1.65rem !important;
        }
    }
</style>
@endpush

@php
    $yearNow = $selectedYear ?? (int) date('Y');
    $totalPeriodIncome = $transaction->sum('total');
    $totalPeriodQty = $transaction->sum('qty');
    $totalPeriodCount = $transaction->count();
    $avgPeriodIncome = $totalPeriodCount > 0 ? ($totalPeriodIncome / $totalPeriodCount) : 0;
    
    $startDateFormatted = \Carbon\Carbon::parse($start_date)->translatedFormat('d M Y');
    $endDateFormatted = \Carbon\Carbon::parse($end_date)->translatedFormat('d M Y');
@endphp

@section('header-actions')
    <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-sm-end gap-2 w-100 w-sm-auto">
        {{-- Year Filter Dropdown --}}
        <div class="dropdown flex-grow-1 flex-sm-grow-0">
            <button type="button" class="btn btn-white bg-white border border-slate-200 shadow-xs rounded-2 px-3 py-1.5 d-inline-flex align-items-center justify-content-between gap-2 text-xs w-100 w-sm-auto" data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.2s ease;">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center w-6 h-6 rounded-circle bg-blue-50 text-blue-600 border border-blue-100">
                        <x-icons.clock class="w-3.5 h-3.5" />
                    </span>
                    <div class="text-start">
                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.62rem; line-height: 1;">Grafik Tahun</span>
                        <span class="fw-bold text-dark fs-7">{{ $yearNow }}</span>
                    </div>
                </div>
                <x-icons.chevron-down class="w-3.5 h-3.5 text-slate-400 ms-1" />
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-md border border-slate-200 rounded-3 p-1" style="min-width: 150px;">
                <div class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1.5" style="font-size: 0.65rem;">
                    Pilih Tahun Grafik
                </div>
                @foreach($availableYears as $yr)
                    @php $isActive = ($yr == $yearNow); @endphp
                    <a href="{{ route('keuangan.index', ['year' => $yr, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="dropdown-item d-flex align-items-center justify-content-between rounded-2 px-3 py-2 text-xs {{ $isActive ? 'active bg-primary text-white fw-bold' : 'text-slate-700' }}">
                        <span>Tahun {{ $yr }}</span>
                        @if($isActive)
                            <x-icons.check class="w-3.5 h-3.5 text-white ms-2" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Export Excel Button --}}
        @can('download excel')
            <a href="{{ route('keuangan.export', ['start_date' => $start_date, 'end_date' => $end_date]) }}" class="btn btn-success d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 text-xs fw-semibold shadow-sm flex-grow-1 flex-sm-grow-0">
                <x-icons.download class="w-4 h-4" />
                <span>Export Excel</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="space-y-4">

    {{-- 1. KPI SUMMARY METRIC CARDS (SESUAI RENTANG TANGGAL TERFILTER) --}}
    <div class="row g-3">
        {{-- Card 1: Total Omzet Terfilter --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden position-relative h-100" style="background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);">
                <div class="position-absolute end-0 bottom-0 opacity-10 text-white p-2 pointer-events-none d-none d-sm-block" style="transform: translate(15%, 15%);">
                    <x-icons.cash class="w-24 h-24" />
                </div>
                <div class="card-body p-3.5 text-white position-relative z-1 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #BFDBFE;">
                                Omzet Terfilter
                            </span>
                            <div class="p-1.5 rounded-2" style="background: rgba(255, 255, 255, 0.2);">
                                <x-icons.cash class="w-4 h-4 text-white" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold tracking-tight mb-1 font-monospace text-truncate">
                            Rp {{ number_format($totalPeriodIncome, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 mt-2" style="font-size: 0.72rem; color: #DBEAFE;">
                        <span>{{ $startDateFormatted }} - {{ $endDateFormatted }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Unit Barang Terjual --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Unit Terjual
                            </span>
                            <div class="p-1.5 rounded-2 bg-indigo-50 text-indigo-700 border border-indigo-100">
                                <x-icons.package class="w-4 h-4 text-indigo-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-indigo-700 tracking-tight mb-1 font-monospace text-truncate">
                            {{ number_format($totalPeriodQty, 0, ',', '.') }} <span class="fs-6 text-slate-500 fw-semibold">Unit</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span>Total item dalam periode ini</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Total Transaksi --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Total Transaksi
                            </span>
                            <div class="p-1.5 rounded-2 bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <x-icons.receipt class="w-4 h-4 text-emerald-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-emerald-700 tracking-tight mb-1 font-monospace text-truncate">
                            {{ number_format($totalPeriodCount, 0, ',', '.') }} <span class="fs-6 text-slate-500 fw-semibold">Nota</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span>Frekuensi checkout berhasil</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Rata-Rata Nominal per Transaksi --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Rata-Rata / Nota
                            </span>
                            <div class="p-1.5 rounded-2 bg-purple-50 text-purple-700 border border-purple-100">
                                <x-icons.trending-up class="w-4 h-4 text-purple-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-slate-800 tracking-tight mb-1 font-monospace text-truncate">
                            Rp {{ number_format($avgPeriodIncome, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span>Rata-rata nilai per transaksi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. SECTION GRAFIK TAHUNAN (PENDAPATAN & PRODUK TERJUAL) --}}
    <div class="row g-4">
        {{-- Grafik Pendapatan Bulanan --}}
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 fs-6 d-flex align-items-center gap-2">
                            <x-icons.trending-up class="w-5 h-5 text-blue-600" />
                            Grafik Pendapatan Bulanan ({{ $yearNow }})
                        </h4>
                        <span class="text-muted small">Tren akumulasi omzet per bulan</span>
                    </div>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill fw-semibold text-xs font-monospace">
                        12 Bulan
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    <div id="chart-income" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>

        {{-- Grafik Barang Terjual Perbulan --}}
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 fs-6 d-flex align-items-center gap-2">
                            <x-icons.package class="w-5 h-5 text-emerald-600" />
                            Grafik Barang Terjual Bulanan ({{ $yearNow }})
                        </h4>
                        <span class="text-muted small">Perbandingan volume penjualan per produk/paket</span>
                    </div>
                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-pill fw-semibold text-xs font-monospace">
                        {{ count($productNames) }} Produk
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    <div id="chart-products-sold" style="min-height: 300px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. FILTER PERIODE LAPORAN & KONTROL --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <x-icons.search class="w-5 h-5 text-primary" />
                <h5 class="card-title fw-bold text-dark m-0">Filter Rentang Tanggal Laporan</h5>
            </div>
            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill text-xs px-2.5 py-1" onclick="setPresetDate('today')">Hari Ini</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill text-xs px-2.5 py-1" onclick="setPresetDate('this_month')">Bulan Ini</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill text-xs px-2.5 py-1" onclick="setPresetDate('last_month')">Bulan Lalu</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill text-xs px-2.5 py-1" onclick="setPresetDate('this_year')">Tahun Ini</button>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('keuangan.index') }}" id="filterForm">
                <input type="hidden" name="year" value="{{ $yearNow }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="start_date" class="form-label fw-bold text-muted small text-uppercase">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-md rounded-2" value="{{ $start_date }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label for="end_date" class="form-label fw-bold text-muted small text-uppercase">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-md rounded-2" value="{{ $end_date }}">
                    </div>
                    <div class="col-12 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-4 py-2 rounded-2 fw-semibold text-xs flex-grow-1 shadow-sm">
                            <x-icons.search class="w-4 h-4" />
                            <span>Tampilkan Laporan</span>
                        </button>
                        @can('download excel')
                            <a href="{{ route('keuangan.export', ['start_date' => $start_date, 'end_date' => $end_date]) }}" class="btn btn-outline-success d-inline-flex align-items-center justify-content-center gap-1.5 px-3 py-2 rounded-2 fw-semibold text-xs shadow-xs" title="Export Excel">
                                <x-icons.download class="w-4 h-4" />
                                <span class="d-none d-lg-inline">Excel</span>
                            </a>
                        @endcan
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 4. TABEL REKAPITULASI TRANSAKSI LENGKAP --}}
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h4 class="card-title fw-bold text-dark m-0 fs-6 d-flex align-items-center gap-2">
                    <x-icons.receipt class="w-5 h-5 text-blue-600" />
                    Rincian Transaksi Terfilter
                </h4>
                <span class="text-muted small">Periode: <b>{{ $startDateFormatted }}</b> s/d <b>{{ $endDateFormatted }}</b></span>
            </div>
            <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill fw-bold text-xs font-monospace">
                Total: {{ number_format($totalPeriodCount) }} Transaksi
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-vcenter align-middle mb-0 text-nowrap">
                <thead class="bg-light text-muted text-uppercase fs-8 fw-bold">
                    <tr>
                        <th class="ps-3 ps-md-4 py-3">No. Nota & SN</th>
                        <th class="py-3">Pelanggan / Agent</th>
                        <th class="py-3">No. Telp</th>
                        <th class="py-3">Paket / Produk</th>
                        <th class="py-3 text-center">Jumlah</th>
                        <th class="py-3 text-end">Total Nominal</th>
                        <th class="py-3 text-center">Tipe / Status</th>
                        <th class="py-3 text-center pe-3 pe-md-4">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y fs-7">
                    @forelse ($transaction as $item)
                        <tr>
                            {{-- No Nota & Serial Number --}}
                            <td class="ps-3 ps-md-4 py-2.5">
                                <div class="fw-bold text-dark font-monospace">#TRX-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>
                                <span class="badge bg-blue-50 text-blue-700 border border-blue-200 font-monospace" style="font-size: 0.68rem;">
                                    SN: {{ $item->customer->code ?? '-' }}
                                </span>
                            </td>

                            {{-- Nama Pelanggan --}}
                            <td class="py-2.5">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="w-7 h-7 rounded-circle bg-blue-50 text-blue-700 border border-blue-200 fw-bold d-flex align-items-center justify-center fs-8 shrink-0">
                                        {{ strtoupper(substr($item->customer->name ?? 'P', 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->customer->name ?? '-' }}</div>
                                        <div class="text-muted small fs-8">{{ $item->customer->address ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- No Telepon --}}
                            <td class="py-2.5 text-muted font-monospace fs-8">
                                {{ $item->customer->telp ?: '-' }}
                            </td>

                            {{-- Produk / Paket --}}
                            <td class="py-2.5">
                                <span class="fw-bold text-dark">{{ $item->product->name ?? '-' }}</span>
                                @if($item->product && $item->product->code)
                                    <div class="text-muted small fs-8 font-monospace">{{ $item->product->code }}</div>
                                @endif
                            </td>

                            {{-- Qty --}}
                            <td class="text-center py-2.5">
                                <span class="badge bg-light text-dark border px-2 py-1 rounded fw-bold fs-8">
                                    {{ $item->qty }} Unit
                                </span>
                            </td>

                            {{-- Total Nominal --}}
                            <td class="text-end py-2.5 font-monospace fw-bold text-dark">
                                Rp {{ number_format($item->total, 0, ',', '.') }}
                            </td>

                            {{-- Tipe & Status --}}
                            <td class="text-center py-2.5">
                                <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-pill fs-8">
                                    {{ $item->customer->type->name ?? 'Reguler' }}
                                </span>
                                @if($item->customer && $item->customer->status)
                                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-pill fs-8 ms-1">
                                        {{ $item->customer->status->name }}
                                    </span>
                                @endif
                            </td>

                            {{-- Waktu --}}
                            <td class="text-center pe-3 pe-md-4 py-2.5 text-muted fs-8 font-monospace">
                                {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <x-icons.receipt class="w-12 h-12 mx-auto mb-2 text-slate-300" />
                                <div class="fw-semibold text-slate-600">Tidak ada transaksi ditemukan</div>
                                <div class="small text-slate-400">Silakan ubah rentang tanggal filter untuk melihat data lainnya.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($transaction->count() > 0)
                    <tfoot class="bg-light border-top-2">
                        <tr class="fw-bold text-dark">
                            <td colspan="4" class="ps-3 ps-md-4 py-3 text-uppercase fs-7">
                                Total Akumulasi ({{ $transaction->count() }} Transaksi)
                            </td>
                            <td class="text-center py-3 font-monospace">
                                {{ number_format($totalPeriodQty, 0, ',', '.') }} Unit
                            </td>
                            <td class="text-end py-3 font-monospace fs-6 text-primary">
                                Rp {{ number_format($totalPeriodIncome, 0, ',', '.') }}
                            </td>
                            <td colspan="2" class="text-end pe-3 pe-md-4 py-3 text-muted small">
                                Rata-rata: Rp {{ number_format($avgPeriodIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Quick Preset Date Helper
    function setPresetDate(type) {
        const today = new Date();
        const startInput = document.getElementById('start_date');
        const endInput = document.getElementById('end_date');
        
        function formatDate(d) {
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        if (type === 'today') {
            const todayStr = formatDate(today);
            startInput.value = todayStr;
            endInput.value = todayStr;
        } else if (type === 'this_month') {
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            startInput.value = formatDate(firstDay);
            endInput.value = formatDate(today);
        } else if (type === 'last_month') {
            const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
            startInput.value = formatDate(firstDayLastMonth);
            endInput.value = formatDate(lastDayLastMonth);
        } else if (type === 'this_year') {
            const firstDayYear = new Date(today.getFullYear(), 0, 1);
            startInput.value = formatDate(firstDayYear);
            endInput.value = formatDate(today);
        }

        document.getElementById('filterForm').submit();
    }

    document.addEventListener("DOMContentLoaded", function () {
        const monthsList = {!! json_encode($months) !!};
        const incomeMonthly = {!! json_encode($incomeData) !!};

        function formatRupiah(num) {
            return "Rp " + new Intl.NumberFormat("id-ID").format(num);
        }

        // ==========================================
        // 1. CHART PENDAPATAN BULANAN
        // ==========================================
        const optionsIncome = {
            chart: {
                type: "area",
                height: 310,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 700
                }
            },
            stroke: {
                curve: 'smooth',
                width: 3,
                colors: ['#2563EB']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 95, 100],
                    colorStops: [
                        { offset: 0, color: '#3B82F6', opacity: 0.4 },
                        { offset: 100, color: '#EFF6FF', opacity: 0.0 }
                    ]
                }
            },
            series: [{
                name: "Total Pendapatan",
                data: incomeMonthly
            }],
            colors: ['#2563EB'],
            xaxis: {
                categories: monthsList,
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function (value) {
                        if (window.innerWidth < 576 && typeof value === 'string') {
                            return value.substring(0, 3);
                        }
                        return value;
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function (value) {
                        if (value >= 1000000) return "Rp " + (value / 1000000).toFixed(1) + " Jt";
                        if (value >= 1000) return "Rp " + (value / 1000).toFixed(0) + " Rb";
                        return "Rp " + value;
                    }
                }
            },
            grid: {
                borderColor: '#F1F5F9',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return formatRupiah(val);
                    }
                }
            },
            markers: {
                size: 4,
                colors: ['#2563EB'],
                strokeColors: '#FFFFFF',
                strokeWidth: 2,
                hover: { size: 7 }
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: { height: 260 }
                    }
                }
            ]
        };

        const chartIncome = new ApexCharts(document.getElementById('chart-income'), optionsIncome);
        chartIncome.render();

        // ==========================================
        // 2. CHART BARANG TERJUAL BULANAN (MULTI-SERIES)
        // ==========================================
        const palette = ['#10B981', '#6366F1', '#F59E0B', '#EC4899', '#06B6D4', '#8B5CF6'];
        const productSeries = [
            @foreach($productNames as $index => $productName)
            {
                name: "{{ $productName }}",
                data: {!! json_encode(array_values($productsPerMonth[$productName])) !!}
            },
            @endforeach
        ];

        const optionsProducts = {
            chart: {
                type: "line",
                height: 310,
                fontFamily: 'inherit',
                toolbar: { show: false },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 700
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2.5
            },
            colors: palette,
            series: productSeries,
            xaxis: {
                categories: monthsList,
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function (value) {
                        if (window.innerWidth < 576 && typeof value === 'string') {
                            return value.substring(0, 3);
                        }
                        return value;
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function (val) {
                        return Math.floor(val) + " Unit";
                    }
                }
            },
            grid: {
                borderColor: '#F1F5F9',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val + " Unit Terjual";
                    }
                }
            },
            legend: {
                position: 'bottom',
                offsetY: 4,
                fontSize: '11px',
                markers: {
                    width: 8,
                    height: 8,
                    radius: 100
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 4
                }
            },
            markers: {
                size: 3,
                hover: { size: 6 }
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: { height: 280 }
                    }
                }
            ]
        };

        const chartProducts = new ApexCharts(document.getElementById('chart-products-sold'), optionsProducts);
        chartProducts.render();
    });
</script>
@endpush