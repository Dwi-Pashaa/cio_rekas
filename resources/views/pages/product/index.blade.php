@extends('layouts.app')

@section('title', 'Katalog & Stok Produk')
@section('pretitle', 'Master Data')

@section('header-actions')
    <button type="button" id="addBtn" data-bs-toggle="modal" data-bs-target="#modal-simple" 
            class="btn btn-primary d-inline-flex align-items-center gap-2 px-3.5 py-2 rounded-2 shadow-sm fw-semibold">
        <x-icons.plus class="w-4 h-4" />
        <span>Tambah Produk</span>
    </button>
@endsection

@section('content')
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
                <form method="GET" action="{{ route('produk.index') }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-slate-400 ps-3 rounded-start-xl">
                            <x-icons.search class="w-5 h-5 text-slate-400" />
                        </span>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-control form-control-md border-start-0 border-slate-300 ps-1 py-2 text-slate-800" 
                            placeholder="Cari nama barang, kode, kategori, cabang..."
                            style="font-size: 0.925rem;"
                        />
                        @if(request('search'))
                            <a href="{{ route('produk.index') }}" class="btn btn-outline-secondary d-flex align-items-center px-3">
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
                    <th class="py-3.5">Informasi Produk</th>
                    <th class="py-3.5 text-center">Kategori</th>
                    <th class="py-3.5">Cabang / Lokasi</th>
                    <th class="py-3.5 text-center">Stok</th>
                    <th class="py-3.5 text-end">Harga Dasar</th>
                    <th class="py-3.5 text-end">Harga Jual</th>
                    <th class="py-3.5 text-center">Margin Laba</th>
                    <th class="py-3.5 text-center" style="min-width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($products as $item)
                    @php
                        $margin = $item->selling_price - $item->base_price;
                        $isLowStock = $item->stock <= 5;
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td class="text-center text-muted fw-semibold">
                            {{ $loop->iteration + ($products->firstItem() ? $products->firstItem() - 1 : 0) }}
                        </td>

                        {{-- Informasi Produk --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-10 h-10 rounded-3 bg-blue-50 text-blue-700 border border-blue-200 d-flex align-items-center justify-center me-3 shrink-0 shadow-xs">
                                    <x-icons.package class="w-5 h-5 text-blue-600" />
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-dark fs-6 text-truncate" style="max-width: 240px;">
                                        {{ $item->name }}
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="badge bg-light text-primary border font-monospace px-2 py-0.5 rounded fw-semibold" style="font-size: 0.7rem;">
                                            {{ $item->code }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Kategori --}}
                        <td class="text-center">
                            <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle px-2.5 py-1 rounded-pill fw-semibold">
                                {{ optional($item->categori)->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Cabang / Lokasi --}}
                        <td>
                            <div class="d-flex align-items-center gap-1.5 text-slate-700">
                                <x-icons.branch class="w-4 h-4 text-slate-400 shrink-0" />
                                <span class="fw-medium small">{{ optional($item->branch)->name ?? 'Semua Cabang' }}</span>
                            </div>
                        </td>

                        {{-- Stok Barang --}}
                        <td class="text-center">
                            @if($isLowStock)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-bold" title="Stok kritis!">
                                    {{ $item->stock }} Unit &bull; Kritis
                                </span>
                            @else
                                <span class="badge bg-emerald-subtle text-emerald-700 border border-emerald-subtle px-2.5 py-1 rounded-pill fw-bold">
                                    {{ $item->stock }} Unit
                                </span>
                            @endif
                        </td>

                        {{-- Harga Dasar --}}
                        <td class="text-end text-muted small font-monospace">
                            Rp {{ number_format($item->base_price, 0, ',', '.') }}
                        </td>

                        {{-- Harga Jual --}}
                        <td class="text-end fw-bold text-dark fs-6 font-monospace">
                            Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                        </td>

                        {{-- Margin Laba --}}
                        <td class="text-center">
                            @if($margin >= 0)
                                <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-2 fw-semibold font-monospace small">
                                    +Rp {{ number_format($margin, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-2 fw-semibold font-monospace small">
                                    -Rp {{ number_format(abs($margin), 0, ',', '.') }}
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1.5">
                                {{-- Tombol Edit --}}
                                <button 
                                    type="button" 
                                    onclick="editModal('{{ $item->id }}')" 
                                    class="btn btn-sm btn-outline-warning px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Edit Produk"
                                >
                                    <x-icons.edit class="w-4 h-4" />
                                    <span>Edit</span>
                                </button>

                                {{-- Tombol Hapus --}}
                                <button 
                                    type="button" 
                                    onclick="deleteItem('{{ $item->id }}')" 
                                    class="btn btn-sm btn-outline-danger px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Hapus Produk"
                                >
                                    <x-icons.trash class="w-4 h-4" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <x-icons.package class="w-12 h-12 mx-auto mb-2 text-muted opacity-50" />
                            <div class="fw-semibold">Tidak ada data produk ditemukan</div>
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
            Menampilkan <b>{{ $products->firstItem() ?? 0 }}</b> sampai <b>{{ $products->lastItem() ?? 0 }}</b> dari <b>{{ $products->total() }}</b> entri produk
        </p>
        <div class="m-0">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('modal')
    {{-- Modal Tambah / Edit Produk (Modern 2-Kolom Grid) --}}
    <div class="modal fade" id="modal-simple" tabindex="-1" aria-labelledby="modalProductTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <x-icons.package class="w-5 h-5 text-primary" />
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalProductTitle">Tambah Produk Baru</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <input type="hidden" name="type" id="type" value="create">
                    <input type="hidden" name="id" id="id">

                    <div class="row g-3">
                        {{-- Kode Barang --}}
                        <div class="col-md-6">
                            <label for="code" class="form-label fw-bold text-muted small text-uppercase">
                                Kode Barang <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="code" id="code" class="form-control form-control-md rounded-2 font-monospace" placeholder="Contoh: BRG-0012">
                            <span class="invalid-feedback error_code"></span>
                        </div>

                        {{-- Nama Barang --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold text-muted small text-uppercase">
                                Nama Barang <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" class="form-control form-control-md rounded-2" placeholder="Nama produk / barang">
                            <span class="invalid-feedback error_name"></span>
                        </div>

                        {{-- Kategori --}}
                        <div class="col-md-6">
                            <label for="categories_id" class="form-label fw-bold text-muted small text-uppercase">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="categories_id" id="categories_id" class="form-select form-select-md rounded-2">
                                <option value="">Pilih Kategori</option>
                                @foreach ($categories as $ct)
                                    <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback error_categories_id"></span>
                        </div>

                        {{-- Cabang / Kantor --}}
                        <div class="col-md-6">
                            <label for="branch_id" class="form-label fw-bold text-muted small text-uppercase">
                                Cabang / Kantor <span class="text-danger">*</span>
                            </label>
                            <select name="branch_id" id="branch_id" class="form-select form-select-md rounded-2">
                                <option value="">Pilih Cabang</option>
                                @foreach ($branch as $brc)
                                    <option value="{{ $brc->id }}">{{ $brc->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback error_branch_id"></span>
                        </div>

                        {{-- Jumlah Stok --}}
                        <div class="col-md-4">
                            <label for="stock" class="form-label fw-bold text-muted small text-uppercase">
                                Stok Awal <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="stock" id="stock" class="form-control form-control-md rounded-2" placeholder="0" min="0" value="0">
                            <span class="invalid-feedback error_stock"></span>
                        </div>

                        {{-- Harga Dasar (Modal) --}}
                        <div class="col-md-4">
                            <label for="base_price" class="form-label fw-bold text-muted small text-uppercase">
                                Harga Dasar (Beli) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                                <input type="text" name="base_price" id="base_price" class="form-control form-control-md rounded-end-2 font-monospace" placeholder="0">
                            </div>
                            <span class="invalid-feedback error_base_price"></span>
                        </div>

                        {{-- Harga Jual --}}
                        <div class="col-md-4">
                            <label for="selling_price" class="form-label fw-bold text-muted small text-uppercase">
                                Harga Jual <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted fw-bold">Rp</span>
                                <input type="text" name="selling_price" id="selling_price" class="form-control form-control-md rounded-end-2 font-monospace" placeholder="0">
                            </div>
                            <span class="invalid-feedback error_selling_price"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="storeBtn" class="btn btn-primary px-4 rounded-2 fw-semibold">Simpan Produk</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('js')
<script>
    const BASE = "{{ route('produk.index') }}";

    // Sorting selector
    let params = new URLSearchParams(window.location.search);
    $("#sort").change(function() {
        params.set('sort', $(this).val());
        window.location.href = BASE + '?' + params.toString();
    });

    const Toast = Swal.mixin({
        toast: true,
        position: "top-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        }
    });

    function formatRupiah(angka) {
        if (!angka) return "";
        let numberString = angka.toString().replace(/\D/g, ""),
            sisa = numberString.length % 3,
            rupiah = numberString.substr(0, sisa),
            ribuan = numberString.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? "." : "";
            rupiah += separator + ribuan.join(".");
        }
        return rupiah;
    }

    $("#base_price").on('input', function() {
        $(this).val(formatRupiah($(this).val()));
    });

    $("#selling_price").on('input', function() {
        $(this).val(formatRupiah($(this).val()));
    });

    $("#addBtn").click(function() {
        $("#modalProductTitle").html("Tambah Produk Baru");
        $("#code").val("");
        $("#name").val("");
        $("#categories_id").val("");
        $("#branch_id").val("");
        $("#stock").val("0");
        $("#base_price").val("");
        $("#selling_price").val("");
        $("#type").val("create");
        $("#id").val("");
        $(".form-control, .form-select").removeClass("is-invalid");
    });

    $("#storeBtn").click(function() {
        let id = $("#id").val();
        let type = $("#type").val();
        let code = $("#code").val();
        let name = $("#name").val();
        let categories_id = $("#categories_id").val();
        let branch_id = $("#branch_id").val();
        let stock = $("#stock").val();
        let base_price = $("#base_price").val();
        let selling_price = $("#selling_price").val();

        let url = (type === 'create') ? BASE + '/store' : BASE + `/${id}/update`;
        let method = (type === 'create') ? "POST" : "PUT";
        
        $.ajax({
            url: url,
            method: method,
            data: {
                code: code,
                name: name,
                categories_id: categories_id,
                branch_id: branch_id,
                stock: stock,
                base_price: base_price,
                selling_price: selling_price
            },
        }).done(function(response) {
            if (response.errors) {
                $.each(response.errors, function(index, value) {
                    $("#" + index).addClass('is-invalid');
                    $(".error_" + index).html(value);

                    setTimeout(() => {
                        $("#" + index).removeClass('is-invalid');
                        $(".error_" + index).html('');
                    }, 3500);
                });                
            } else {
                $("#modal-simple").modal('hide');
                Toast.fire({
                    icon: response.status || 'success',
                    title: response.message || 'Data produk berhasil disimpan.'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
            Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem.' });
        });
    });

    function editModal(id) {
        let url = BASE + `/${id}/show`;
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            $("#modalProductTitle").html("Edit Data Produk");
            let data = response.data;
            $("#id").val(data.id);
            $("#code").val(data.code);
            $("#name").val(data.name);
            $("#categories_id").val(data.categories_id);
            $("#branch_id").val(data.branch_id);
            $("#stock").val(data.stock);
            $("#base_price").val(formatRupiah(data.base_price));
            $("#selling_price").val(formatRupiah(data.selling_price));
            $("#type").val("update");
            $(".form-control, .form-select").removeClass("is-invalid");
            $("#modal-simple").modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function deleteItem(id) {
        Swal.fire({
            title: "Konfirmasi Hapus",
            text: "Produk ini akan dihapus secara permanen dari basis data.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#EF4444",
            cancelButtonColor: "#64748B",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                popup: 'rounded-3',
                confirmButton: 'rounded-2 font-semibold px-4 py-2',
                cancelButton: 'rounded-2 font-medium px-4 py-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE + '/' + id + '/destroy',
                    method: "DELETE",
                    dataType: "json",
                    success: function(response) {
                        Toast.fire({
                            icon: response.status || 'success',
                            title: response.message || 'Produk berhasil dihapus.'
                        });

                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        Toast.fire({
                            icon: "error",
                            title: "Gagal menghapus data produk."
                        });
                    }
                });
            }
        });
    }
</script>
@endpush