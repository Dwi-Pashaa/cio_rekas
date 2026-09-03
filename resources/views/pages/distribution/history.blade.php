@extends('layouts.app')

@section('title', 'Riwayat Distribusi')
@section('pretitle', 'Audit & Log Distribusi')

@section('content')
<div class="card shadow-sm border-0 rounded-3 mb-4">
    {{-- Header Filters & Search Bar --}}
    <div class="card-body border-bottom py-3 px-4">
        <div class="row g-3 align-items-center justify-content-between">
            {{-- Filter Types --}}
            <div class="col-12 col-md-auto d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('distribution.history') }}" 
                   class="btn btn-sm rounded-pill fw-semibold {{ !$type ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Semua Mutasi
                </a>
                <a href="{{ route('distribution.history', ['type' => 'admin_to_utama']) }}" 
                   class="btn btn-sm rounded-pill fw-semibold {{ $type === 'admin_to_utama' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Topup Kuota
                </a>
                <a href="{{ route('distribution.history', ['type' => 'utama_to_cabang']) }}" 
                   class="btn btn-sm rounded-pill fw-semibold {{ $type === 'utama_to_cabang' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Topup &rarr; Cabang
                </a>
                <a href="{{ route('distribution.history', ['type' => 'cabang_to_branch']) }}" 
                   class="btn btn-sm rounded-pill fw-semibold {{ $type === 'cabang_to_branch' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Alokasi Ke Kantor
                </a>
            </div>

            {{-- Search Bar --}}
            <div class="col-12 col-md-4 ms-auto">
                <form method="GET" action="{{ route('distribution.history') }}">
                    @if($type)
                        <input type="hidden" name="type" value="{{ $type }}">
                    @endif
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-slate-400 ps-3">
                            <x-icons.search class="w-4 h-4 text-slate-400" />
                        </span>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-control form-control-sm border-start-0 border-slate-300 ps-1" 
                            placeholder="Cari ref, voucher, pengirim, kantor..."
                        />
                        @if(request('search'))
                            <a href="{{ route('distribution.history', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                                <x-icons.close class="w-3.5 h-3.5" />
                            </a>
                        @endif
                        <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Responsive Table --}}
    <div class="table-responsive">
        <table class="table table-hover table-vcenter align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3.5">No</th>
                    <th class="py-3.5">No. Referensi</th>
                    <th class="py-3.5">Jenis Mutasi</th>
                    <th class="py-3.5">Nama Voucher</th>
                    <th class="py-3.5 text-center">Jumlah</th>
                    <th class="py-3.5 text-center">Sebelum / Sesudah</th>
                    <th class="py-3.5">Pengirim</th>
                    <th class="py-3.5">Penerima / Tujuan</th>
                    <th class="py-3.5">Catatan</th>
                    <th class="py-3.5">Waktu Transaksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($histories as $item)
                    <tr>
                        <td class="text-center text-muted fw-semibold">
                            {{ $loop->iteration + ($histories->firstItem() ? $histories->firstItem() - 1 : 0) }}
                        </td>
                        <td>
                            <span class="font-monospace fw-bold text-primary">{{ $item->reference_no }}</span>
                        </td>
                        <td>
                            @if($item->type === 'admin_to_utama')
                                <span class="badge bg-purple-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-purple me-1"></span> Topup Kuota
                                </span>
                            @elseif($item->type === 'utama_to_cabang')
                                <span class="badge bg-blue-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-blue me-1"></span> Topup &rarr; Cabang
                                </span>
                            @else
                                <span class="badge bg-green-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-green me-1"></span> Alokasi Ke Kantor
                                </span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ $item->product_name }}</td>
                        <td class="text-center">
                            <span class="fw-bold fs-6 text-dark">+{{ number_format($item->qty, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center font-monospace text-muted small">
                            @if($item->stock_before !== null && $item->stock_after !== null)
                                {{ number_format($item->stock_before, 0, ',', '.') }} &rarr; <span class="fw-bold text-dark">{{ number_format($item->stock_after, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->sender->name ?? 'System' }}</td>
                        <td>
                            @if($item->receiver)
                                <span class="fw-semibold text-indigo-700">{{ $item->receiver->name }}</span>
                            @elseif($item->targetBranch)
                                <span class="fw-semibold text-emerald-700">Kantor {{ $item->targetBranch->name }}</span>
                            @else
                                <span class="text-muted">Pool Utama</span>
                            @endif
                        </td>
                        <td class="text-muted small text-truncate" style="max-width: 220px;" title="{{ $item->notes }}">
                            {{ $item->notes ?? '-' }}
                        </td>
                        <td class="text-muted small">{{ $item->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            Tidak ada riwayat mutasi distribusi yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($histories->hasPages())
        <div class="card-footer bg-transparent border-top py-3 px-4">
            {{ $histories->links() }}
        </div>
    @endif
</div>
@endsection
