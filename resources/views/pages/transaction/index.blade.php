@extends('layouts.app')

@section('title', 'Riwayat Transaksi')
@section('pretitle', 'Data Penjualan Kasir')

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        {{-- Badge Stok Cepat di Topbar untuk User Cabang (Non-Admin) --}}
        @if(isset($branchStock))
            <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-2 text-xs fw-semibold text-blue-800" style="height: 38px;">
                <x-icons.package class="w-4 h-4 text-blue-600" />
                <span>Stok {{ $userBranchName ?? 'Cabang' }}: <b class="text-primary">{{ number_format($branchStock, 0, ',', '.') }} Unit</b></span>
            </div>
        @endif

        @can('tambah transaksi')
            <a href="{{ route('transaksi.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 shadow-sm fw-semibold">
                <x-icons.plus class="w-4 h-4" />
                <span>Transaksi Baru</span>
            </a>
        @endcan

        @can('download excel')
            <a href="{{ route('transaksi.export') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 fw-semibold">
                <x-icons.download class="w-4 h-4" />
                <span>Export Excel</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
{{-- Banner Ringkasan Stok Khusus User Cabang (Non-Admin) --}}
@if(isset($branchStock))
    <div class="card border-0 rounded-3 text-white mb-3 shadow-sm overflow-hidden" 
         style="background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%);">
        <div class="card-body py-3 px-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-2 bg-white text-blue-800 d-flex align-items-center justify-center shadow-xs shrink-0" style="width: 44px; height: 44px;">
                    <x-icons.package class="w-6 h-6 text-blue-700" />
                </div>
                <div>
                    <div class="text-uppercase fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.08em; color: #BFDBFE;">
                        INVENTARIS AKTIF &bull; {{ strtoupper($userBranchName ?? 'CABANG ANDA') }}
                    </div>
                    <div class="fw-bold fs-4 text-white lh-1 mt-0.5">
                        {{ number_format($branchStock, 0, ',', '.') }} <span class="fs-6 fw-normal text-blue-100">Unit Tersedia</span>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                @if(($lowStockCount ?? 0) > 0)
                    <span class="badge bg-amber-400 text-slate-950 fw-bold px-2.5 py-1.5 rounded-pill text-xs">
                        {{ $lowStockCount }} Item Menipis
                    </span>
                @else
                    <span class="badge bg-emerald-400 text-slate-950 fw-bold px-2.5 py-1.5 rounded-pill text-xs">
                        Stok Aman
                    </span>
                @endif

                @can('tambah transaksi')
                    <a href="{{ route('transaksi.create') }}" class="btn btn-light btn-sm px-3 py-1.5 rounded-2 fw-bold text-blue-800 shadow-xs d-inline-flex align-items-center gap-1.5">
                        <x-icons.cart class="w-4 h-4 text-blue-700" />
                        <span>Buka Kasir POS</span>
                    </a>
                @endcan
            </div>
        </div>
    </div>
@endif

<div class="card shadow-sm border-0 rounded-3 mb-4">
    
    {{-- Header & Search Filter Bar --}}
    <div class="card-body border-bottom py-3 px-4">
        <div class="row g-3 align-items-center justify-content-between">
            {{-- Filter Sort Dropdown --}}
            <div class="col-12 col-md-auto d-flex align-items-center gap-2.5">
                <label for="sort" class="text-slate-600 fw-semibold fs-6 m-0">Tampilkan:</label>
                <select name="sort" id="sort" class="form-select form-select-md w-auto rounded-xl fw-semibold border-slate-300 py-2 px-3 shadow-none">
                    @foreach ([10, 25, 50, 100] as $opt)
                        <option value="{{ $opt }}" {{ request('sort') == $opt ? 'selected' : '' }}>{{ $opt }} baris</option>
                    @endforeach
                </select>
            </div>

            {{-- Search Bar --}}
            <div class="col-12 col-md-5 ms-auto">
                <form method="GET" action="{{ route('transaksi.index') }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-slate-400 ps-3 rounded-start-xl">
                            <x-icons.search class="w-5 h-5 text-slate-400" />
                        </span>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-control form-control-md border-start-0 border-slate-300 ps-1 py-2 text-slate-800" 
                            placeholder="Cari agent, serial number, kasir, barang..."
                            style="font-size: 0.925rem;"
                        />
                        @if(request('search'))
                            <a href="{{ route('transaksi.index') }}" class="btn btn-outline-secondary d-flex align-items-center px-3">
                                <x-icons.close class="w-4 h-4" />
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-4 fw-bold rounded-end-xl">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Responsive Table Container --}}
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3.5">No</th>
                    <th class="py-3.5">Agent</th>
                    <th class="py-3.5">Barang / Paket</th>
                    <th class="py-3.5 text-center">Qty</th>
                    <th class="py-3.5 text-end">Total Bayar</th>
                    <th class="py-3.5">Kasir & Lokasi Transaksi</th>
                    <th class="py-3.5 text-center">Status</th>
                    <th class="py-3.5">Waktu Transaksi</th>
                    <th class="py-3.5 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($transaction as $item)
                    @php
                        $stockBranch = optional(optional($item->product)->branch)->name ?? (optional($item->branch)->name ?? 'Semua Cabang');
                        $agentHomeBranch = optional(optional(optional($item->customer)->product)->branch)->name;
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td class="text-center text-muted fw-semibold">
                            {{ $loop->iteration + ($transaction->firstItem() ? $transaction->firstItem() - 1 : 0) }}
                        </td>

                        {{-- Agent & Serial Number --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-9 h-9 rounded-circle bg-blue-50 text-blue-700 border border-blue-200 fw-bold d-flex align-items-center justify-center me-2.5 shrink-0 text-xs">
                                    {{ strtoupper(substr($item->customer->name ?? 'A', 0, 2)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark fs-6">{{ $item->customer->name ?? 'Agent Umum' }}</div>
                                    <div class="d-flex align-items-center flex-wrap gap-1.5 text-muted small mt-0.5">
                                        <span class="font-monospace text-primary fw-semibold">SN: {{ $item->customer->code ?? '-' }}</span>
                                        @if($agentHomeBranch && $agentHomeBranch !== $stockBranch)
                                            <span class="badge bg-light text-slate-600 border px-1.5 py-0.2 rounded" style="font-size: 0.65rem;" title="Cabang asal pendaftaran agent">
                                                Asal: {{ $agentHomeBranch }}
                                            </span>
                                        @endif
                                        @if($item->customer && $item->customer->telp)
                                            <span>&bull;</span>
                                            <span>{{ $item->customer->telp }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Barang / Paket & Cabang Asal Stok --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $item->product->name ?? '-' }}</div>
                            <div class="d-flex align-items-center flex-wrap gap-1.5 mt-1">
                                <span class="text-muted small font-monospace">Kode: {{ $item->product->code ?? '-' }}</span>
                                <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.68rem;" title="Stok barang diambil dari cabang ini">
                                    <x-icons.package class="w-3 h-3 inline me-0.5" />
                                    Stok: {{ $stockBranch }}
                                </span>
                            </div>
                        </td>

                        {{-- Qty --}}
                        <td class="text-center">
                            <span class="badge bg-light text-slate-800 border px-2.5 py-1 rounded-2 fw-bold">
                                {{ $item->qty }} Unit
                            </span>
                        </td>

                        {{-- Total Bayar --}}
                        <td class="text-end fw-extrabold text-dark fs-6 font-monospace">
                            Rp {{ number_format($item->total, 0, ',', '.') }}
                        </td>

                        {{-- Kasir & Cabang Lokasi Transaksi --}}
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <div class="fw-semibold text-slate-800">{{ optional($item->casier)->name ?? '-' }}</div>
                                    <div class="d-flex align-items-center gap-1 small mt-0.5">
                                        <x-icons.branch class="w-3.5 h-3.5 text-slate-400" />
                                        <span class="badge bg-blue-50 text-blue-700 border border-blue-200 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                            {{ optional($item->branch)->name ?? (optional($item->casier->branch)->name ?? 'Semua Cabang') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Status Pelanggan --}}
                        <td class="text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold">
                                {{ optional(optional($item->customer)->status)->name ?? 'Lunas' }}
                            </span>
                        </td>

                        {{-- Waktu Transaksi --}}
                        <td class="text-muted small">
                            <div>{{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}</div>
                            <div class="text-slate-400" style="font-size: 0.72rem;">{{ $item->created_at ? $item->created_at->format('H:i:s') . ' WIB' : '' }}</div>
                        </td>

                        {{-- Aksi Struk --}}
                        <td class="text-center">
                            <button 
                                type="button" 
                                onclick="printReceipt('{{ $item->id }}')" 
                                class="btn btn-sm btn-outline-primary px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-none" 
                                title="Cetak Struk Thermal"
                            >
                                <x-icons.printer class="w-4 h-4" />
                                <span>Struk</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <x-icons.receipt class="w-12 h-12 mx-auto mb-2 text-muted opacity-50" />
                            <div class="fw-semibold">Tidak ada data transaksi ditemukan</div>
                            @if(request('search'))
                                <div class="small text-muted mt-1">Coba gunakan kata kunci pencarian yang lain.</div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer Pagination --}}
    <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between py-3 px-4 gap-2 bg-white border-top">
        <p class="m-0 text-muted small">
            Menampilkan <b>{{ $transaction->firstItem() ?? 0 }}</b> sampai <b>{{ $transaction->lastItem() ?? 0 }}</b> dari <b>{{ $transaction->total() }}</b> transaksi
        </p>
        <div class="m-0">
            {{ $transaction->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const BASE = "{{ route('transaksi.index') }}";

    // Sorting selector
    let params = new URLSearchParams(window.location.search);
    $("#sort").change(function() {
        params.set('sort', $(this).val());
        window.location.href = BASE + '?' + params.toString();
    });

    function printReceipt(transactionId) {
        let receiptUrl = "{{ url('transaction') }}/" + transactionId + "/receipt";
        let popupWidth = 480;
        let popupHeight = 650;
        let left = (window.screen.width - popupWidth) / 2;
        let top = (window.screen.height - popupHeight) / 2;

        let printWindow = window.open(
            receiptUrl,
            "_blank",
            `width=${popupWidth},height=${popupHeight},top=${top},left=${left}`
        );

        if (printWindow) {
            printWindow.focus();
        } else {
            alert("Izinkan popup browser untuk mencetak struk transaksi.");
        }
    }
</script>
@endpush