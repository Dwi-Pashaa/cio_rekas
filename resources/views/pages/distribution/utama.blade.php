@extends('layouts.app')

@section('title', 'Topup Kuota Voucher')
@section('pretitle', 'Manajemen Distribusi')

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        @if(auth()->user()->hasRole('Admin'))
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-topup">
                <x-icons.plus class="w-4 h-4" />
                <span>Topup Kuota Voucher</span>
            </button>
        @endif
        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modal-distribute">
            <x-icons.truck class="w-4 h-4" />
            <span>Kirim Kuota ke Cabang</span>
        </button>
    </div>
@endsection

@section('content')
<div class="row g-3 mb-4">
    {{-- Metric 1: Total Stok Utama --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Total Kuota Voucher Utama</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($utamaStocks->sum('stock'), 0, ',', '.') }} <span class="fs-6 fw-normal text-muted">pcs</span></h3>
                </div>
                <div class="w-12 h-12 rounded-3 bg-blue-50 text-blue-600 border border-blue-100 d-flex align-items-center justify-center shrink-0">
                    <x-icons.package class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Metric 2: Varian Produk --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Varian Voucher Terdaftar</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $utamaStocks->count() }} <span class="fs-6 fw-normal text-muted">item</span></h3>
                </div>
                <div class="w-12 h-12 rounded-3 bg-emerald-50 text-emerald-600 border border-emerald-100 d-flex align-items-center justify-center shrink-0">
                    <x-icons.categories class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    {{-- Metric 3: User Distribusi Cabang Siap Terima --}}
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 rounded-3 h-100">
            <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fw-semibold small text-uppercase tracking-wider d-block">Distributor Cabang Aktif</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ $eligibleUsers->count() }} <span class="fs-6 fw-normal text-muted">user</span></h3>
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
            <span>Saldo Kuota Voucher Utama</span>
        </h4>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3">No</th>
                    <th class="py-3">Nama Voucher / Produk</th>
                    <th class="py-3 text-center">Tipe Alokasi</th>
                    <th class="py-3 text-center">Sisa Stok Kuota</th>
                    <th class="py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($utamaStocks as $stock)
                    <tr>
                        <td class="text-center text-muted fw-semibold">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-9 h-9 rounded-2 bg-blue-50 text-blue-600 d-flex align-items-center justify-center me-3 shrink-0">
                                    <x-icons.package class="w-5 h-5" />
                                </div>
                                <span class="fw-bold text-dark">{{ $stock->product_name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-blue-lt px-2.5 py-1 rounded-pill fw-semibold">
                                Pool Kuota Utama
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold fs-5 {{ $stock->stock > 0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($stock->stock, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($stock->stock > 50)
                                <span class="badge bg-green-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-green me-1"></span> Stok Tersedia
                                </span>
                            @elseif($stock->stock > 0)
                                <span class="badge bg-yellow-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-yellow me-1"></span> Stok Menipis
                                </span>
                            @else
                                <span class="badge bg-red-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-red me-1"></span> Habis
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada stok voucher di Kuota Utama. Admin dapat menambahkan stok melalui tombol "Topup Kuota Voucher".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Distribution History Table --}}
<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
        <h4 class="card-title fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <x-icons.clock class="w-5 h-5 text-indigo-600" />
            <span>Riwayat Mutasi Distribusi</span>
        </h4>
        <a href="{{ route('distribution.history') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-vcenter align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3">No</th>
                    <th class="py-3">No. Referensi</th>
                    <th class="py-3">Jenis Mutasi</th>
                    <th class="py-3">Voucher</th>
                    <th class="py-3 text-center">Jumlah (Qty)</th>
                    <th class="py-3">Pengirim</th>
                    <th class="py-3">Penerima / Tujuan</th>
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
                            @if($history->type === 'admin_to_utama')
                                <span class="badge bg-purple-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-purple me-1"></span> Topup Kuota
                                </span>
                            @elseif($history->type === 'utama_to_cabang')
                                <span class="badge bg-blue-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-blue me-1"></span> Topup &rarr; Cabang
                                </span>
                            @else
                                <span class="badge bg-green-lt px-2.5 py-1 rounded-pill fw-semibold">
                                    <span class="badge-dot bg-green me-1"></span> Alokasi Ke Kantor
                                </span>
                            @endif
                        </td>
                        <td class="fw-semibold text-dark">{{ $history->product_name }}</td>
                        <td class="text-center fw-bold text-dark">+{{ number_format($history->qty, 0, ',', '.') }}</td>
                        <td>{{ $history->sender->name ?? 'System' }}</td>
                        <td>
                            @if($history->receiver)
                                <span class="fw-semibold text-indigo-700">{{ $history->receiver->name }}</span>
                            @elseif($history->targetBranch)
                                <span class="fw-semibold text-emerald-700">Kantor {{ $history->targetBranch->name }}</span>
                            @else
                                <span class="text-muted">Pool Utama</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $history->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat mutasi distribusi.</td>
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

{{-- MODAL 1: TOP UP ADMIN --}}
@if(auth()->user()->hasRole('Admin'))
<div class="modal modal-blur fade" id="modal-topup" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded-3 border-0">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <x-icons.plus class="w-5 h-5 text-primary" />
                    <span>Topup Kuota Voucher</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTopup">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Pilih Barang dari Cabang</label>
                        <select name="product_name" id="topup_product_name" class="form-select rounded-2" required>
                            <option value="">-- Pilih Barang dari Cabang --</option>
                            @foreach($productList as $prod)
                                <option value="{{ $prod->name }}">
                                    {{ $prod->name }} &bull; Cabang {{ $prod->branch->name ?? 'Pusat' }} (Kode: {{ $prod->code }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_topup_product_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jumlah Voucher (Qty)</label>
                        <input type="number" name="qty" id="topup_qty" class="form-control rounded-2" placeholder="Contoh: 1000" min="1" required />
                        <div class="invalid-feedback" id="err_topup_qty"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan (Opsional)</label>
                        <textarea name="notes" id="topup_notes" class="form-control rounded-2" rows="2" placeholder="Catatan alokasi stok..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitTopup" class="btn btn-primary px-4 fw-semibold">
                        <span>Tambah Stok</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- MODAL 2: DISTRIBUTE TO CABANG --}}
<div class="modal modal-blur fade" id="modal-distribute" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded-3 border-0">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <x-icons.truck class="w-5 h-5 text-primary" />
                    <span>Distribusi Stok ke Akun Distribusi Cabang</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formDistribute">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">User Distribusi Cabang Penerima</label>
                        <select name="receiver_id" id="dist_receiver_id" class="form-select rounded-2" required>
                            <option value="">-- Pilih User Penerima --</option>
                            @forelse($eligibleUsers as $usr)
                                <option value="{{ $usr->id }}">
                                    {{ $usr->name }} ({{ $usr->username }}) {{ $usr->branch ? '- Cabang ' . $usr->branch->name : '' }}
                                </option>
                            @empty
                                <option value="" disabled>-- Belum ada user dengan hak akses Distribusi Cabang --</option>
                            @endforelse
                        </select>
                        <div class="invalid-feedback" id="err_dist_receiver_id"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jenis Voucher</label>
                        <select name="product_name" id="dist_product_name" class="form-select rounded-2" required>
                            <option value="">-- Pilih Jenis Voucher --</option>
                            @foreach($utamaStocks as $stk)
                                <option value="{{ $stk->product_name }}" data-stock="{{ $stk->stock }}">
                                    {{ $stk->product_name }} (Stok Utama: {{ number_format($stk->stock, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="err_dist_product_name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Jumlah Voucher (Qty)</label>
                        <input type="number" name="qty" id="dist_qty" class="form-control rounded-2" placeholder="Contoh: 250" min="1" required />
                        <div class="invalid-feedback" id="err_dist_qty"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan Pengiriman</label>
                        <textarea name="notes" id="dist_notes" class="form-control rounded-2" rows="2" placeholder="Keterangan pengiriman stok..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitDistribute" class="btn btn-primary px-4 fw-semibold">
                        <span>Kirim Stok</span>
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

    // Handle Admin Topup Submit
    $("#formTopup").on("submit", function(e) {
        e.preventDefault();
        const $btn = $("#btnSubmitTopup");
        $btn.prop("disabled", true).text("Memproses...");
        $(".form-control, .form-select").removeClass("is-invalid");

        $.ajax({
            url: "{{ route('distribution.utama.topup') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.code === 200) {
                    Toast.fire({ icon: 'success', title: response.message });
                    $("#modal-topup").modal('hide');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    $btn.prop("disabled", false).text("Tambah Stok");
                    Toast.fire({ icon: 'error', title: response.message });
                }
            },
            error: function(xhr) {
                $btn.prop("disabled", false).text("Tambah Stok");
                if (xhr.status === 400 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.product_name) $("#topup_product_name").addClass("is-invalid").siblings("#err_topup_product_name").text(errors.product_name[0]);
                    if (errors.qty) $("#topup_qty").addClass("is-invalid").siblings("#err_topup_qty").text(errors.qty[0]);
                } else {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Terjadi kesalahan pada server.' });
                }
            }
        });
    });

    // Handle Distribute to Cabang Submit
    $("#formDistribute").on("submit", function(e) {
        e.preventDefault();
        const $btn = $("#btnSubmitDistribute");
        $btn.prop("disabled", true).text("Mengirim...");
        $(".form-control, .form-select").removeClass("is-invalid");

        $.ajax({
            url: "{{ route('distribution.utama.distribute') }}",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                if (response.code === 200) {
                    Toast.fire({ icon: 'success', title: response.message });
                    $("#modal-distribute").modal('hide');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    $btn.prop("disabled", false).text("Kirim Stok");
                    Toast.fire({ icon: 'error', title: response.message });
                }
            },
            error: function(xhr) {
                $btn.prop("disabled", false).text("Kirim Stok");
                if (xhr.status === 400 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.receiver_id) $("#dist_receiver_id").addClass("is-invalid").siblings("#err_dist_receiver_id").text(errors.receiver_id[0]);
                    if (errors.product_name) $("#dist_product_name").addClass("is-invalid").siblings("#err_dist_product_name").text(errors.product_name[0]);
                    if (errors.qty) $("#dist_qty").addClass("is-invalid").siblings("#err_dist_qty").text(errors.qty[0]);
                } else {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Terjadi kesalahan sistem.' });
                }
            }
        });
    });
</script>
@endpush
