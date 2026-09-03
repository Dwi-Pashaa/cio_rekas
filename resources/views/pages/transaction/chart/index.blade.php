@extends('layouts.app')

@section('title', 'Grafik Penjualan & Analitik')
@section('pretitle', 'Laporan & Statistik')

@push('css')
<style>
    /* Styling khusus ApexCharts Tooltip agar selalu bersih, kontras, dan rapi */
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
    .apexcharts-datalabels text {
        font-family: inherit !important;
    }
    
    /* Responsive typography helper */
    @media (max-width: 575.98px) {
        .kpi-value {
            font-size: 1.35rem !important;
        }
        .agent-name-cell {
            max-width: 110px !important;
        }
    }
    @media (min-width: 576px) {
        .kpi-value {
            font-size: 1.65rem !important;
        }
        .agent-name-cell {
            max-width: 180px !important;
        }
    }
</style>
@endpush

@section('header-actions')
    <div class="d-flex flex-wrap align-items-center justify-content-start justify-content-sm-end gap-2 w-100 w-sm-auto">
        {{-- Custom Modern Year Filter Dropdown --}}
        <div class="dropdown flex-grow-1 flex-sm-grow-0">
            <button type="button" class="btn btn-white bg-white border border-slate-200 shadow-xs rounded-2 px-3 py-1.5 d-inline-flex align-items-center justify-content-between gap-2 text-xs w-100 w-sm-auto" data-bs-toggle="dropdown" aria-expanded="false" style="transition: all 0.2s ease;">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center w-6 h-6 rounded-circle bg-blue-50 text-blue-600 border border-blue-100">
                        <x-icons.clock class="w-3.5 h-3.5" />
                    </span>
                    <div class="text-start">
                        <span class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.62rem; line-height: 1;">Periode</span>
                        <span class="fw-bold text-dark fs-7">Tahun {{ $selectedYear ?? date('Y') }}</span>
                    </div>
                </div>
                <x-icons.chevron-down class="w-3.5 h-3.5 text-slate-400 ms-1" />
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-md border border-slate-200 rounded-3 p-1" style="min-width: 150px;">
                <div class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1.5" style="font-size: 0.65rem;">
                    Pilih Tahun
                </div>
                @foreach($availableYears as $yr)
                    @php $isActive = ($yr == ($selectedYear ?? date('Y'))); @endphp
                    <a href="{{ route('transaksi.chart', ['year' => $yr]) }}" class="dropdown-item d-flex align-items-center justify-content-between rounded-2 px-3 py-2 text-xs {{ $isActive ? 'active bg-primary text-white fw-bold' : 'text-slate-700' }}">
                        <span>Tahun {{ $yr }}</span>
                        @if($isActive)
                            <x-icons.check class="w-3.5 h-3.5 text-white ms-2" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        @can('lihat transaksi')
            <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 text-xs fw-semibold flex-grow-1 flex-sm-grow-0">
                <x-icons.receipt class="w-4 h-4" />
                <span class="d-none d-md-inline">Riwayat Transaksi</span>
                <span class="d-inline d-md-none">Riwayat</span>
            </a>
        @endcan

        @can('tambah transaksi')
            <a href="{{ route('transaksi.create') }}" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 rounded-2 text-xs fw-semibold shadow-sm flex-grow-1 flex-sm-grow-0">
                <x-icons.plus class="w-4 h-4" />
                <span>Transaksi Baru</span>
            </a>
        @endcan
    </div>
@endsection

@php
    $yearNow = $selectedYear ?? (int) date('Y');
    $totalAnnualIncome = array_sum($incomeData);
    $activeMonthsCount = count(array_filter($incomeData, fn($val) => $val > 0)) ?: 1;
    $averageMonthlyIncome = $totalAnnualIncome / 12;
    $maxIncome = max($incomeData);
    $maxIncomeIndex = array_search($maxIncome, $incomeData);
    $peakMonthName = $maxIncome > 0 ? ($months[$maxIncomeIndex] ?? '-') : '-';
    
    $totalAgentUnits = $topCustomers->sum('total_spent');
    $totalAgentNominal = $topCustomers->sum('total_nominal');
    $currentMonthIndex = ($yearNow == (int) date('Y')) ? ((int) date('n') - 1) : -1;
@endphp

@section('content')
<div class="space-y-4">
    
    {{-- 1. KPI SUMMARY CARDS --}}
    <div class="row g-3">
        {{-- Card 1: Total Pendapatan Tahunan --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden position-relative h-100" style="background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);">
                <div class="position-absolute end-0 bottom-0 opacity-10 text-white p-2 pointer-events-none d-none d-sm-block" style="transform: translate(15%, 15%);">
                    <x-icons.cash class="w-24 h-24" />
                </div>
                <div class="card-body p-3.5 text-white position-relative z-1 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #BFDBFE;">
                                Total Pendapatan {{ $yearNow }}
                            </span>
                            <div class="p-1.5 rounded-2" style="background: rgba(255, 255, 255, 0.2);">
                                <x-icons.cash class="w-4 h-4 text-white" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold tracking-tight mb-1 font-monospace text-truncate">
                            Rp {{ number_format($totalAnnualIncome, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 mt-2" style="font-size: 0.72rem; color: #DBEAFE;">
                        <span>Akumulasi 12 Bulan Tahun {{ $yearNow }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Rata-Rata Bulanan --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Rata-Rata / Bulan
                            </span>
                            <div class="p-1.5 rounded-2 bg-blue-50 text-blue-700 border border-blue-100">
                                <x-icons.trending-up class="w-4 h-4 text-blue-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-slate-800 tracking-tight mb-1 font-monospace text-truncate">
                            Rp {{ number_format($averageMonthlyIncome, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span>Rata-rata penjualan per bulan</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Bulan Pendapatan Tertinggi --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Bulan Tertinggi (Peak)
                            </span>
                            <div class="p-1.5 rounded-2 bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <x-icons.trending-up class="w-4 h-4 text-emerald-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-emerald-700 tracking-tight mb-1 text-truncate">
                            {{ $peakMonthName }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span class="fw-bold text-slate-700 font-monospace">Rp {{ number_format($maxIncome, 0, ',', '.') }}</span>
                        <span>rekor tertinggi</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 4: Kontribusi Top Agent --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-slate-500" style="font-size: 0.7rem; letter-spacing: 0.08em;">
                                Volume Top Agent
                            </span>
                            <div class="p-1.5 rounded-2 bg-purple-50 text-purple-700 border border-purple-100">
                                <x-icons.users class="w-4 h-4 text-purple-600" />
                            </div>
                        </div>
                        <div class="kpi-value fw-extrabold text-purple-700 tracking-tight mb-1 font-monospace text-truncate">
                            {{ number_format($totalAgentUnits, 0, ',', '.') }} <span class="fs-6 text-slate-500 fw-semibold">Unit</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 text-slate-500 mt-2" style="font-size: 0.72rem;">
                        <span class="fw-bold text-slate-700 font-monospace">Rp {{ number_format($totalAgentNominal, 0, ',', '.') }}</span>
                        <span>total omzet</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. MAIN SECTION: GRAFIK TREN PENDAPATAN & REKAP BULANAN --}}
    <div class="row g-4">
        {{-- Grafik Tren Pendapatan (Area Spline) --}}
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 d-flex align-items-center gap-2 fs-6">
                            <x-icons.trending-up class="w-5 h-5 text-primary" />
                            Tren Pendapatan Bulanan ({{ $yearNow }})
                        </h4>
                        <span class="text-muted small">Visualisasi performa penjualan dari bulan Januari hingga Desember</span>
                    </div>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill fw-semibold text-xs font-monospace">
                        12 Bulan
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    <div id="chart-monthly-income" style="min-height: 330px;"></div>
                </div>
            </div>
        </div>

        {{-- Tabel Rekapitulasi Pendapatan Bulanan --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 fs-6">Rekapitulasi Bulanan</h4>
                        <span class="text-muted small">Detail nominal per bulan ({{ $yearNow }})</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
                        <table class="table table-hover table-vcenter align-middle mb-0 text-nowrap">
                            <thead class="bg-light text-muted text-uppercase fs-8 fw-bold sticky-top">
                                <tr>
                                    <th class="ps-3 ps-md-4 py-2.5">Bulan</th>
                                    <th class="py-2.5 text-end pe-3 pe-md-4">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y fs-7">
                                @foreach($months as $index => $month)
                                    @php
                                        $nominal = (float) $incomeData[$index];
                                        $percent = $maxIncome > 0 ? ($nominal / $maxIncome) * 100 : 0;
                                        $isCurrentMonth = ($index === $currentMonthIndex);
                                    @endphp
                                    <tr class="{{ $isCurrentMonth ? 'bg-blue-50/50 fw-bold' : '' }}">
                                        <td class="ps-3 ps-md-4 py-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-dark">{{ $month }}</span>
                                                @if($isCurrentMonth)
                                                    <span class="badge bg-primary text-white rounded-pill px-1.5 py-0.5" style="font-size: 0.62rem;">Bulan Ini</span>
                                                @endif
                                                @if($nominal == $maxIncome && $maxIncome > 0)
                                                    <span class="badge bg-emerald-100 text-emerald-800 rounded-pill px-1.5 py-0.5" style="font-size: 0.62rem;">Peak</span>
                                                @endif
                                            </div>
                                            {{-- Mini progress bar --}}
                                            <div class="progress mt-1" style="height: 3px; background-color: #E2E8F0; width: 90px;">
                                                <div class="progress-bar bg-blue-600 rounded" role="progressbar" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 pe-md-4 py-2 font-monospace {{ $nominal > 0 ? 'text-dark fw-bold' : 'text-muted' }}">
                                            Rp {{ number_format($nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. BOTTOM SECTION: ANALISIS TOP AGENT (CHART & LEADERBOARD) --}}
    <div class="row g-4">
        {{-- Grafik Top 10 Agent --}}
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 fs-6 d-flex align-items-center gap-2">
                            <x-icons.barcode class="w-5 h-5 text-indigo-600" />
                            Grafik Top 10 Agent Teraktif
                        </h4>
                        <span class="text-muted small">Peringkat agen berdasarkan akumulasi unit pembelian ({{ $yearNow }})</span>
                    </div>
                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-2.5 py-1 rounded-pill fw-bold text-xs">
                        Top 10 Agent
                    </span>
                </div>
                <div class="card-body p-2 p-md-4">
                    @if($topCustomers->count() > 0)
                        <div id="chart-top-customers" style="min-height: 400px;"></div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <x-icons.users class="w-12 h-12 mx-auto mb-2 text-slate-300" />
                            <div class="fw-semibold">Belum ada data transaksi agent di tahun {{ $yearNow }}</div>
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-light border-top py-2.5 px-3 px-md-4 text-muted small d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>* Label unit tertera di ujung bar</span>
                    <span class="fw-semibold text-dark font-monospace">Total: {{ number_format($totalAgentUnits, 0, ',', '.') }} Unit</span>
                </div>
            </div>
        </div>

        {{-- Leaderboard Tabel Top Agent --}}
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="card-title fw-bold text-dark m-0 fs-6 d-flex align-items-center gap-2">
                            <x-icons.users class="w-5 h-5 text-blue-600" />
                            Peringkat Agent Terbaik (Leaderboard)
                        </h4>
                        <span class="text-muted small">Rincian performa dan total belanja agent ({{ $yearNow }})</span>
                    </div>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill fw-bold text-xs">
                        Leaderboard
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                        <table class="table table-hover table-vcenter align-middle mb-0 text-nowrap">
                            <thead class="bg-light text-muted text-uppercase fs-8 fw-bold sticky-top">
                                <tr>
                                    <th class="w-1 text-center py-3 ps-3 ps-md-4">Rank</th>
                                    <th class="py-3">Agent</th>
                                    <th class="py-3 text-center">Unit Terjual</th>
                                    <th class="py-3 text-end pe-3 pe-md-4">Total Belanja</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($topCustomers as $rank => $agent)
                                    <tr>
                                        {{-- Rank Medal / Badge --}}
                                        <td class="text-center ps-3 ps-md-4 py-2.5">
                                            @if($rank == 0)
                                                <span class="badge bg-amber-100 text-amber-900 border border-amber-300 rounded-circle fw-extrabold d-inline-flex align-items-center justify-center shadow-xs" style="width: 24px; height: 24px; font-size: 0.72rem;">1</span>
                                            @elseif($rank == 1)
                                                <span class="badge bg-slate-200 text-slate-800 border border-slate-300 rounded-circle fw-extrabold d-inline-flex align-items-center justify-center shadow-xs" style="width: 24px; height: 24px; font-size: 0.72rem;">2</span>
                                            @elseif($rank == 2)
                                                <span class="badge bg-amber-700/20 text-amber-900 border border-amber-600/30 rounded-circle fw-extrabold d-inline-flex align-items-center justify-center shadow-xs" style="width: 24px; height: 24px; font-size: 0.72rem;">3</span>
                                            @else
                                                <span class="text-muted fw-semibold fs-7">{{ $rank + 1 }}</span>
                                            @endif
                                        </td>

                                        {{-- Agent Avatar & Info --}}
                                        <td class="py-2.5">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="w-7 h-7 rounded-circle bg-blue-50 text-blue-700 border border-blue-200 fw-bold d-flex align-items-center justify-center fs-8 shrink-0">
                                                    {{ strtoupper(substr($agent->customer_name, 0, 2)) }}
                                                </div>
                                                <div class="overflow-hidden">
                                                    <div class="fw-bold text-dark fs-7 text-truncate agent-name-cell">{{ $agent->customer_name }}</div>
                                                    <div class="text-muted small fs-8">{{ $agent->customer_telp ?: '-' }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Total Unit --}}
                                        <td class="text-center py-2.5">
                                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-2 fw-bold fs-8">
                                                {{ number_format($agent->total_spent, 0, ',', '.') }} Unit
                                            </span>
                                        </td>

                                        {{-- Total Nominal Belanja --}}
                                        <td class="text-end pe-3 pe-md-4 py-2.5 font-monospace fw-bold text-dark fs-7">
                                            Rp {{ number_format($agent->total_nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted fs-7">
                                            Belum ada data transaksi agent di tahun {{ $yearNow }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light border-top py-2.5 px-3 px-md-4 text-muted small d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>* Total omzet top 10 agent</span>
                    <span class="fw-semibold text-primary font-monospace">Rp {{ number_format($totalAgentNominal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const monthsList = {!! json_encode($months) !!};
        const monthlyData = {!! json_encode($incomeData) !!};

        // Format Rupiah Helper
        function formatRupiah(num) {
            return "Rp " + new Intl.NumberFormat("id-ID").format(num);
        }

        // Format Number Helper
        function formatNumber(num) {
            return new Intl.NumberFormat("id-ID").format(num);
        }

        // ==========================================
        // 1. CHART TREN PENDAPATAN BULANAN (SPLINE AREA)
        // ==========================================
        const optionsIncome = {
            series: [{
                name: "Total Pendapatan",
                data: monthlyData
            }],
            chart: {
                type: 'area',
                height: 350,
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            dataLabels: {
                enabled: false
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
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: '#3B82F6', opacity: 0.4 },
                        { offset: 100, color: '#EFF6FF', opacity: 0.0 }
                    ]
                }
            },
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
                        // Singkat nama bulan di layar mobile (misal: Jan, Feb, Mar)
                        if (window.innerWidth < 576 && typeof value === 'string') {
                            return value.substring(0, 3);
                        }
                        return value;
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748B',
                        fontSize: '11px',
                        fontWeight: 500
                    },
                    formatter: function (value) {
                        if (value >= 1000000) {
                            return "Rp " + (value / 1000000).toFixed(1) + " Jt";
                        } else if (value >= 1000) {
                            return "Rp " + (value / 1000).toFixed(0) + " Rb";
                        }
                        return "Rp " + value;
                    }
                }
            },
            grid: {
                borderColor: '#F1F5F9',
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true
                    }
                }
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
                hover: {
                    size: 7
                }
            },
            responsive: [
                {
                    breakpoint: 768,
                    options: {
                        chart: { height: 280 },
                        yaxis: {
                            labels: {
                                style: { fontSize: '10px' },
                                formatter: function (value) {
                                    if (value >= 1000000) return (value / 1000000).toFixed(0) + "Jt";
                                    return value;
                                }
                            }
                        }
                    }
                }
            ]
        };

        const chartIncome = new ApexCharts(document.getElementById('chart-monthly-income'), optionsIncome);
        chartIncome.render();

        // ==========================================
        // 2. CHART TOP 10 AGENT (HORIZONTAL BAR)
        // ==========================================
        const topCustomerNames = {!! json_encode($topCustomers->pluck('customer_name')) !!};
        const topCustomerSpent = {!! json_encode($topCustomers->pluck('total_spent')) !!};
        const topCustomerNominal = {!! json_encode($topCustomers->pluck('total_nominal')) !!};

        if (topCustomerNames.length > 0) {
            const optionsTopAgent = {
                series: [{
                    name: "Unit Pembelian",
                    data: topCustomerSpent
                }],
                chart: {
                    type: "bar",
                    height: 420,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 700
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 6,
                        borderRadiusApplication: 'end',
                        barHeight: '62%',
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    offsetX: 8,
                    style: {
                        fontSize: '11px',
                        fontFamily: 'inherit',
                        fontWeight: 700,
                        colors: ['#1E293B']
                    },
                    formatter: function (val) {
                        return formatNumber(val) + " Unit";
                    }
                },
                colors: ['#4F46E5'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: 'horizontal',
                        shadeIntensity: 0.15,
                        gradientToColors: ['#3B82F6'],
                        inverseColors: false,
                        opacityFrom: 1,
                        opacityTo: 0.95,
                        stops: [0, 100]
                    }
                },
                xaxis: {
                    categories: topCustomerNames,
                    tickAmount: 4,
                    labels: {
                        style: {
                            colors: '#64748B',
                            fontSize: '11px',
                            fontWeight: 500
                        },
                        formatter: function (value) {
                            if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return (value / 1000).toLocaleString('id-ID') + 'k';
                            return value;
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        maxWidth: 170,
                        style: {
                            colors: '#0F172A',
                            fontSize: '12px',
                            fontWeight: 600
                        }
                    }
                },
                grid: {
                    borderColor: '#F1F5F9',
                    strokeDashArray: 4,
                    xaxis: {
                        lines: { show: true }
                    },
                    yaxis: {
                        lines: { show: false }
                    }
                },
                tooltip: {
                    theme: 'light',
                    custom: function({ series, seriesIndex, dataPointIndex, w }) {
                        const name = topCustomerNames[dataPointIndex];
                        const units = topCustomerSpent[dataPointIndex];
                        const nominal = topCustomerNominal[dataPointIndex];

                        return `
                            <div style="padding: 10px 14px; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 14px rgba(0,0,0,0.12); border: 1px solid #e2e8f0; font-family: inherit;">
                                <div style="font-weight: 700; color: #0f172a; font-size: 13px; margin-bottom: 4px;">${name}</div>
                                <div style="color: #2563eb; font-weight: 700; font-size: 12px; margin-bottom: 2px;">${formatNumber(units)} Unit Paket</div>
                                <div style="color: #64748b; font-size: 11px; font-family: monospace;">Total: ${formatRupiah(nominal)}</div>
                            </div>
                        `;
                    }
                },
                responsive: [
                    {
                        breakpoint: 768,
                        options: {
                            chart: { height: 380 },
                            yaxis: {
                                labels: {
                                    maxWidth: 110,
                                    style: { fontSize: '10px' }
                                }
                            },
                            dataLabels: {
                                style: { fontSize: '9.5px' }
                            }
                        }
                    },
                    {
                        breakpoint: 480,
                        options: {
                            chart: { height: 350 },
                            plotOptions: {
                                bar: { barHeight: '75%' }
                            },
                            yaxis: {
                                labels: {
                                    maxWidth: 85,
                                    style: { fontSize: '9.5px' }
                                }
                            },
                            dataLabels: {
                                style: { fontSize: '8.5px' }
                            }
                        }
                    }
                ]
            };

            const chartTopAgent = new ApexCharts(document.getElementById('chart-top-customers'), optionsTopAgent);
            chartTopAgent.render();
        }
    });
</script>
@endpush