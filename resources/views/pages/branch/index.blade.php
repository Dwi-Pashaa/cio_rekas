@extends('layouts.app')

@section('title', 'Data Cabang')
@section('pretitle', 'Master Data')

@section('header-actions')
    @can('tambah cabang')
        <x-ui.button id="addBtn" variant="primary" size="md" data-bs-toggle="modal" data-bs-target="#modal-branch">
            <x-icons.plus class="w-4 h-4 mr-1.5" />
            <span>Tambah Cabang</span>
        </x-ui.button>
    @endcan
@endsection

@section('content')
<div class="space-y-6">
    <x-ui.card-table 
        title="Daftar Cabang" 
        subtitle="Manajemen data cabang dan lokasi unit usaha"
        :paginator="$branch"
    >
        <x-slot:actions>
            <x-ui.search-input placeholder="Cari nama atau No. WhatsApp cabang..." />
        </x-slot:actions>

        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3.5 w-16 text-center">No</th>
                    <th class="px-5 py-3.5">Nama Cabang</th>
                    <th class="px-5 py-3.5">No. WhatsApp Kasir/Cabang</th>
                    <th class="px-5 py-3.5">Tanggal Dibuat</th>
                    <th class="px-5 py-3.5 text-center w-36">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($branch as $item)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-5 py-3.5 text-center text-slate-400 font-medium">
                            {{ $loop->iteration + ($branch->firstItem() ? $branch->firstItem() - 1 : 0) }}
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="font-bold text-slate-900">{{ $item->name }}</div>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($item->wa_number)
                                <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-1 rounded-pill font-monospace fw-semibold" style="font-size: 0.75rem;">
                                    {{ $item->wa_number }}
                                </span>
                            @else
                                <span class="text-slate-400 fst-italic text-xs">Belum diatur</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1.5">
                                @can('edit cabang')
                                    <button 
                                        type="button" 
                                        onclick="editModal('{{ $item->id }}')" 
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
                                        title="Edit Cabang"
                                    >
                                        <x-icons.edit class="w-4 h-4" />
                                    </button>
                                @endcan
                                @can('hapus cabang')
                                    <button 
                                        type="button" 
                                        onclick="deleteItem('{{ $item->id }}')" 
                                        class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" 
                                        title="Hapus Cabang"
                                    >
                                        <x-icons.trash class="w-4 h-4" />
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                            <x-icons.branch class="w-10 h-10 mx-auto mb-2 text-slate-300" />
                            <p class="text-sm font-medium">Belum ada data cabang</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-ui.card-table>
</div>
@endsection

@push('modal')
    <x-ui.modal id="modal-branch" title="Tambah Cabang" size="md">
        <input type="hidden" name="type" id="type" value="create">
        <input type="hidden" name="id" id="id">
        
        <x-ui.form-input 
            label="Nama Cabang" 
            name="name" 
            id="name" 
            placeholder="Contoh: Cabang Pacet / Cabang Mojokerto" 
            required 
        />

        <div class="mt-3">
            <x-ui.form-input 
                label="No. WhatsApp Kasir / Cabang" 
                name="wa_number" 
                id="wa_number" 
                placeholder="Contoh: 08xxxxxxxxxx (untuk notifikasi pesanan agent)" 
            />
            <small class="text-slate-500 text-xs mt-1 block">
                Nomor ini akan menerima notifikasi WhatsApp otomatis setiap kali ada Agent yang membuat pesanan dari cabang ini.
            </small>
        </div>

        <x-slot:footer>
            <button type="button" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all cursor-pointer" data-bs-dismiss="modal">
                Batal
            </button>
            <button type="button" id="storeBtn" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-sm transition-all cursor-pointer">
                Simpan Cabang
            </button>
        </x-slot:footer>
    </x-ui.modal>
@endpush

@push('js')
<script>
    const BASE = "{{ route('branch.index') }}";

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

    $("#addBtn").click(function() {
        $("#modal-branchLabel").html("Tambah Cabang");
        $("#name").val("");
        $("#wa_number").val("");
        $("#type").val("create");
        $("#id").val("");
    });

    $("#storeBtn").click(function() {
        let id = $("#id").val();
        let type = $("#type").val();
        let name = $("#name").val();
        let wa_number = $("#wa_number").val();

        let url = (type === 'create') ? BASE + '/store' : BASE + `/${id}/update`;
        let method = (type === 'create') ? "POST" : "PUT";
        
        $.ajax({
            url: url,
            method: method,
            data: { 
                name: name,
                wa_number: wa_number
            },
        }).done(function(response) {
            if (response.errors) {
                $.each(response.errors, function(index, value) {
                    $("#" + index).addClass('border-rose-500 focus:ring-rose-500');
                    $(".error_" + index).html(value);

                    setTimeout(() => {
                        $("#" + index).removeClass('border-rose-500 focus:ring-rose-500');
                        $(".error_" + index).html('');
                    }, 3000);
                });                
            } else {
                $("#modal-branch").modal('hide');
                Toast.fire({
                    icon: response.status || 'success',
                    title: response.message || 'Data cabang berhasil disimpan.'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
            Toast.fire({ icon: 'error', title: 'Terjadi kesalahan pada server.' });
        });
    });

    function editModal(id) {
        $.ajax({
            url: BASE + `/${id}/show`,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            $("#modal-branchLabel").html("Edit Cabang");
            let data = response.data;
            $("#id").val(data.id);
            $("#name").val(data.name);
            $("#wa_number").val(data.wa_number || "");
            $("#type").val("update");
            $("#modal-branch").modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function deleteItem(id) {
        Swal.fire({
            title: "Konfirmasi Hapus",
            text: "Data cabang ini akan dihapus secara permanen.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#EF4444",
            cancelButtonColor: "#64748B",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold px-4 py-2',
                cancelButton: 'rounded-xl font-medium px-4 py-2'
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
                            title: response.message || 'Cabang berhasil dihapus.'
                        });

                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        Toast.fire({
                            icon: "error",
                            title: "Gagal menghapus cabang."
                        });
                    }
                });
            }
        });
    }
</script>
@endpush