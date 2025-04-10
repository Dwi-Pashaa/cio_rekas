@extends('layouts.app')

@section('title')
    Data Pelanggan
@endsection

@push('css')
    
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="javascript:void(0)" id="addBtn" data-bs-toggle="modal" data-bs-target="#modal-simple" class="btn btn-primary m-2">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Tambah
            </a>
            @can('download excel')
                <a href="{{ route('customer.export') }}" class="btn btn-success">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                    Download Excel
                </a>
            @endcan
        </div>
        <div class="card-body border-bottom py-3">
            <div class="d-flex">
                <div class="text-secondary">
                    <div class="mx-2 d-inline-block">
                        <select name="sort" id="sort" class="form-control">
                            @php
                                $opts = [
                                    10,25,50,100
                                ];
                            @endphp 
                            @foreach ($opts as $opt)
                                <option value="{{ $opt }}" {{ request('sort') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ms-auto text-secondary">
                    <form>
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" name="search" placeholder="Search for…">
                            <button class="btn" type="submit">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
                        <th class="w-1">No</th>
                        <th>Barcode</th>
                        <th>Nama Pelanggan</th>
                        <th>No Telephone</th>
                        <th>Alamat</th>
                        <th>Barang</th>
                        <th>Limit</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                   @forelse ($customers as $item)
                       <tr>
                            <td>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($item->code, 'C128') }}" alt="barcode" />
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->telp }}</td>
                            <td>{{ $item->address }}</td>
                            <td>{{ optional($item->product)->code }} - {{ optional($item->product)->name }}</td>
                            <td>{{ $item->limit }}</td>
                            <td>{{ optional($item->type)->name ?? '-' }}</td>
                            <td>{{ optional($item->status)->name ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <a href="javascript:void(0)" onclick="return editModal('{{ $item->id }}')" class="btn btn-outline-warning btn-md">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                    Edit
                                </a>
                                <a href="javascript:void(0)" onclick="return deleteItem('{{ $item->id }}')" class="btn btn-outline-danger btn-md">
                                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                    Hapus
                                </a>
                            </td> 
                       </tr>
                   @empty
                       <tr>
                            <td colspan="11" class="text-center">Tidak Ada Data</td>
                       </tr>
                   @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex align-items-center">
            <p class="m-0 text-secondary">
                Showing <span>{{ $customers->firstItem() }}</span> 
                to <span>{{ $customers->lastItem() }}</span> of
                <span>{{ $customers->total() }}</span> entries
            </p>
            <ul class="pagination m-0 ms-auto">
                {{ $customers->links() }}
            </ul>
        </div>
    </div>
@endsection

@push('modal')
    <div class="modal modal-blur fade" id="modal-simple" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-1 modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="type" id="type">
                    <input type="hidden" name="id" id="id">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="code" class="mb-2">Serial Number</label>
                                <input type="text" name="code" id="code" class="form-control">
                                <span class="invalid-feedback error_code"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="name" class="mb-2">Nama Pelanggan</label>
                                <input type="text" name="name" id="name" class="form-control">
                                <span class="invalid-feedback error_name"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="telp" class="mb-2">No Telephone</label>
                                <input type="text" name="telp" id="telp" class="form-control">
                                <span class="invalid-feedback error_telp"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="address" class="mb-2">Alamat</label>
                                <input type="text" name="address" id="address" class="form-control">
                                <span class="invalid-feedback error_address"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="products_id" class="mb-2">Barang</label>
                                <select name="products_id" id="products_id" class="form-control">
                                    <option value="">Pilih</option>
                                    @foreach ($products as $pd)
                                        <option value="{{ $pd->id }}">{{ $pd->code }} - {{ $pd->name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback error_products_id"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="limit" class="mb-2">Limit</label>
                                <input type="text" name="limit" id="limit" class="form-control">
                                <span class="invalid-feedback error_limit"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="types_id" class="mb-2">Type Pelanggan</label>
                                <select name="types_id" id="types_id" class="form-control">
                                    <option value="">Pilih</option>
                                    @foreach ($customerTypes as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback error_types_id"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="form-group mb-3">
                                <label for="status_id" class="mb-2">Status Pelanggan</label>
                                <select name="status_id" id="status_id" class="form-control">
                                    <option value="">Pilih</option>
                                    @foreach ($customerStatus as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback error_status_id"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="storeBtn" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('js')
<script>
    const BASE = "{{ route('customer.index') }}";

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

    $("#base_price").keyup(function() {
        let value = $(this).val()
        $("#base_price").val(formatRupiah(value))
    })

    $("#selling_price").keyup(function() {
        let value = $(this).val()
        $("#selling_price").val(formatRupiah(value))
    })

    $("#addBtn").click(function() {
        $(".modal-title").html("Tambah Pelanggan");
        $("#code").val("");
        $("#name").val("");
        $("#telp").val("");
        $("#address").val("");
        $("#products_id").val("");
        $("#limit").val("");
        $("#type_customer").val("");
        $("#status").val("")
        $("#type").val("create");
        $("#id").val("");
    });

    $("#storeBtn").click(function() {
        let id = $("#id").val();
        let type = $("#type").val()
        let code = $("#code").val();
        let name = $("#name").val();
        let telp = $("#telp").val();
        let address = $("#address").val();
        let products_id = $("#products_id").val();
        let limit = $("#limit").val();
        let types_id= $("#types_id").val();
        let status_id = $("#status_id").val()

        let url;
        let method;

        if (type === 'create') {
            url = BASE + '/store';
            method = "POST";
        } else {
            url = BASE + `/${id}/update`
            method = "PUT";
        }
        
        $.ajax({
            url: url,
            method: method,
            data: {
                code: code,
                name: name,
                telp: telp,
                address: address,
                products_id: products_id,
                limit: limit,
                types_id: types_id,
                status_id: status_id
            },
        }).done(function(response) {
            if (response.errors) {
                $.each(response.errors, function(index, value) {
                    console.log(value);
                    
                    $("#" + index).addClass('is-invalid');
                    $(".error_" + index).html(value);

                    setTimeout(() => {
                        $("#" + index).removeClass('is-invalid');
                        $(".error_" + index).html('');
                    }, 3000);
                })                
            } else {
                $("#modal-simple").modal('hide')
                Toast.fire({
                    icon: response.status,
                    title: response.message
                });

                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    });

    function editModal(id) {
        let url = BASE + `/${id}/show`
        $.ajax({
            url: url,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            $(".modal-title").html("Edit Pelanggan");
            let data = response.data;
            $("#modal-simple").modal('show')

            $("#id").val(data.id);
            $("#code").val(data.code);
            $("#name").val(data.name);
            $("#telp").val(data.telp);
            $("#address").val(data.address);
            $("#products_id").val(data.products_id);
            $("#limit").val(data.limit);
            $("#types_id").val(data.types_id);
            $("#status_id").val(data.status_id)
            $("#type").val("update");
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function deleteItem(id) {
        Swal.fire({
            title: "Peringatan !",
            text: "Anda yakin ingin menghapus data ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Hapus",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE + '/' + id + '/destroy',
                    method: "DELETE",
                    dataType: "json",
                    success: function(response) {
                        Toast.fire({
                            icon: response.status,
                            title: response.message
                        });

                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);
                    },
                    error: function(err) {
                        Toast.fire({
                            icon: "error",
                            title: "Server Error"
                        });
                    }
                })
            }
        });
    }
</script>
@endpush