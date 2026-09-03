@extends('layouts.app')

@section('title', 'Distribusi Utama')
@section('pretitle', 'Manajemen Distribusi')

@section('header-actions')
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-distribute-branch">
        <x-icons.branch class="w-4 h-4" />
        <span>Alokasi Ke Kantor</span>
    </button>
@endsection

@section('content')
<div class="row g-3 mb-4">
    {{-- Metric 1: Total Saldo Stok Akun Cabang --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Total Saldo Stok Voucher Anda</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($cabangStocks->sum('stock'), 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">pcs</span></h3>
                </div>
                <div class="w-12 h-12 rounded-3 bg-blue-50 text-blue-600 border border-blue-100 d-flex align-items-center justify-center shrink-0">
                    <x-icons.package class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Metric 2: Total Kantor Cabang Tersedia --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Kantor Cabang Terdaftar</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $branches->count() }} <span class="fs-6 fw-normal text-muted">lokasi</span></h3>
                </div>
                <div class="w-12 h-12 rounded-3 bg-emerald-50 text-emerald-600 border border-emerald-100 d-flex align-items-center justify-center shrink-0">
                    <x-icons.branch class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Metric 3: User Info --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Akun Distributor Cabang</span>
                    <h4 class="fw-bold text-dark mt-1 mb-0 text-truncate" style="max-width: 200px;">{{ auth()->user()->name }}</h4>
                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 mt-1 small">
                        {{ auth()->user()->branch->name ?? 'Distribusi Regional' }}
                    </span>
                </div>
                <div class="w-12 h-12 rounded-3 bg-indigo-50 text-indigo-600 border border-indigo-100 d-flex align-items-center justify-center shrink-0">
                    <x-icons.users class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock List Table --}}
<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <x-icons.package class="w-5 h-5 text-primary" />
            <span>Saldo Stok Voucher Cabang</span>
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3">No</th>
                    @if(auth()->user()->hasRole('Admin') || auth()->user()->can('distribusi utama'))
                        <th class="py-3">Pemegang Stok</th>
                    @endif
                    <th class="py-3">Nama Voucher / Produk</th>
                    <th class="py-3 text-center">Sisa Stok Siap Alokasi</th>
                    <th class="py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($cabangStocks as $stock)
                    <tr>
                        <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                        @if(auth()->user()->hasRole('Admin') || auth()->user()->can('distribusi utama'))
                            <td>
                                <div class="fw-bold text-dark">{{ $stock->user->name ?? 'User' }}</div>
                                <div class="text-muted small">{{ $stock->user->branch->name ?? 'Regional' }}</div>
                            </td>
                        @endif
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-9 h-9 rounded-2 bg-blue-50 text-blue-600 d-flex align-items-center justify-center me-3 shrink-0">
                                    <x-icons.package class="w-5 h-5" />
                                </div>
                                <span class="fw-bold text-dark">{{ $stock->product_name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold fs-5 {{ $stock->stock > 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($stock->stock, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($stock->stock > 20)
                                <span class="badge bg-green-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-green me-1"></span> Tersedia
                                </span>
                            @elseif($stock->stock > 0)
                                <span class="badge bg-yellow-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-yellow me-1"></span> Stok Rendah
                                </span>
                            @else
                                <span class="badge bg-red-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-red me-1"></span> Kosong
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (auth()->user()->hasRole('Admin') || auth()->user()->can('distribusi utama')) ? 5 : 4 }}" class="text-center py-4 text-muted">
                            Belum ada saldo stok voucher yang dialokasikan ke akun Cabang Anda. Silakan hubungi bagian Distribusi Utama / Admin.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent History Table --}}
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <x-icons.clock class="w-5 h-5 text-indigo-600" />
            <span>Riwayat Alokasi Ke Kantor</span>
        </h4>
        <a href="{{ route('distribution.history', ['type' => 'cabang_to_branch']) }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3">No</th>
                    <th class="py-3">No. Referensi</th>
                    <th class="py-3">Kantor Cabang Tujuan</th>
                    <th class="py-3">Jenis Voucher</th>
                    <th class="py-3 text-center">Jumlah (Qty)</th>
                    <th class="py-3">Pengirim</th>
                    <th class="py-3">Catatan</th>
                    <th class="py-3">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($histories as $history)
                    <tr>
                        <td class="text-center text-muted fw-semibold">
                            {{ $loop->iteration + ($histories->firstItem() ? $histories->firstItem() - 1 : 0) }}
                        </td>
                        <td>
                            <span class="font-monospace fw-bold text-primary">{{ $history->reference_no }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-8 h-8 rounded-2 bg-emerald-50 text-emerald-600 d-flex align-items-center justify-center me-2 shrink-0">
                                    <x-icons.branch class="w-4 h-4" />
                                </div>
                                <span class="fw-bold text-emerald-800">Kantor {{ $history->targetBranch->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="fw-semibold text-dark">{{ $history->product_name }}</td>
                        <td class="text-center fw-bold text-dark">+{{ number_format($history->qty, 0, ',', '.') }}</td>
                        <td>{{ $history->sender->name ?? 'System' }}</td>
                        <td class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $history->notes }}">
                            {{ $history->notes ?? '-' }}
                        </td>
                        <td class="text-muted small">{{ $history->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat alokasi stok ke kantor cabang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($histories->hasPages())
        <div class="card-footer bg-transparent border-top py-2 px-4">
            {{ $histories->links() }}
        </div>
    @endif
</div>

{{-- MODAL DISTRIBUTE TO BRANCH / KANTOR --}}
<div class="modal modal-blur fade" id="modal-distribute-branch" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded-3 border-0">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <x-icons.branch class="w-5 h-5 text-primary" />
                    <span>Alokasi Stok Voucher Ke Kantor</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDistributeBranch">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Pilih Kantor Cabang Tujuan</label>
                        <select name="target_branch_id" id="branch_target_id" class="form-select rounded-2" required>
                            <option value="">-- Pilih Kantor Cabang --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_branch_target_id"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jenis Voucher</label>
                        <select name="product_name" id="branch_product_name" class="form-select rounded-2" required>
                            <option value="">-- Pilih Jenis Voucher --</option>
                            @foreach($cabangStocks as $stk)
                                <option value="{{ $stk->product_name }}" data-stock="{{ $stk->stock }}">
                                    {{ $stk->product_name }} (Saldo Anda: {{ number_format($stk->stock, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_branch_product_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jumlah Voucher (Qty)</label>
                        <input type="number" name="qty" id="branch_qty" class="form-control rounded-2" placeholder="Contoh: 100" min="1" required />
                        <div class="invalid-feedback" id="err_branch_qty"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Alokasi</label>
                        <textarea name="notes" id="branch_notes" class="form-control rounded-2" rows="2" placeholder="Keterangan alokasi stok ke kantor..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitDistBranch" class="btn btn-primary px-4 fw-semibold">
                        <span>Alokasi Ke Kantor</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true
    });

    $("#formDistributeBranch").on("submit", function(e) {
        e.preventDefault();
        const $btn = $("#btnSubmitDistBranch");
        $btn.prop("disabled", true).text("Mengalokasikan...");
        $(".form-control, .form-select").removeClass("is-invalid");

        $.ajax({
            url: "{{ route('distribution.cabang.distribute') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.code === 200) {
                    Toast.fire({ icon: 'success', title: response.message });
                    $("#modal-distribute-branch").modal('hide');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    $btn.prop("disabled", false).text("Alokasi Ke Kantor");
                    Toast.fire({ icon: 'error', title: response.message });
                }
            },
            error: function(xhr) {
                $btn.prop("disabled", false).text("Alokasi Ke Kantor");
                if (xhr.status === 400 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.target_branch_id) $("#branch_target_id").addClass("is-invalid").siblings("#err_branch_target_id").text(errors.target_branch_id[0]);
                    if (errors.product_name) $("#branch_product_name").addClass("is-invalid").siblings("#err_branch_product_name").text(errors.product_name[0]);
                    if (errors.qty) $("#branch_qty").addClass("is-invalid").siblings("#err_branch_qty").text(errors.qty[0]);
                } else {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.' });
                }
            }
        });
    });
</script>
@endpush
