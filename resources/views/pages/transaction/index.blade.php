@extends('layouts.app')

@section('title')
    List Transaksi
@endsection

@push('css')
    
@endpush

@section('content')

@include('components.alert.success')

<div class="row mb-3">
    <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="card">
            <div class="table-responsive-lg">
                <table class="table card-table table-vcenter text-nowrap datatable">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $index => $month)
                        <tr>
                            <td>{{ $month }}</td>
                            <td>Rp. {{ number_format($incomeData[$index], 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-8 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex">
                    <h3 class="card-title">Grafik Pembelian Terbanyak</h3>
                </div>
                <div id="chart-top-customer"></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <a href="{{ route('transaksi.create') }}" class="btn btn-primary m-2">
            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Tambah
        </a>
        @can('download excel')
            <a href="{{ route('transaksi.export') }}" class="btn btn-success">
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
                    <th>Kasir</th>
                    <th>No Serial</th>
                    <th>Nama Penjual</th>
                    <th>No Telephone</th>
                    <th>Alamat</th>
                    <th>Barang</th>
                    <th>Jumlah</th>
                    <th>Nominal</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transaction as $item)
                    <tr>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ optional($item->casier)->name ?? '-'; }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->code }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->telp }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->address }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->product->code }} - {{ $item->product->name }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->qty }}
                            </a>
                        </td>
                        <td>
                            Rp. {{ number_format($item->total) }}
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->type }}
                            </a>
                        </td>
                        <td>
                            <a href="#" class="text-reset" tabindex="-1">
                                {{ $item->customer->status }}
                            </a>
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <a href="javascript:void(0)" onclick="return printReceipt('{{ $item->id }}');" class="btn btn-outline-warning btn-md">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-printer"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                Cetak
                            </a>
                            {{-- <a href="javascript:void(0)" onclick="return deletetransaction('{{ $item->id }}')" class="btn btn-outline-danger btn-md">
                                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                Hapus
                            </a> --}}
                        </td> 
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center">Tidak Ada Data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex align-items-center">
        <p class="m-0 text-secondary">
            Showing <span>{{ $transaction->firstItem() }}</span> 
            to <span>{{ $transaction->lastItem() }}</span> of
            <span>{{ $transaction->total() }}</span> entries
        </p>
        <ul class="pagination m-0 ms-auto">
            {{ $transaction->links() }}
        </ul>
    </div>
</div>
@endsection

@push('js')
    <script>
        const BASE = "{{ route('transaksi.index') }}";

        let params = new URLSearchParams(window.location.search);
        $("#sort").change(function() {
            params.set('sort', $(this).val());
            window.location.href = BASE + '?' + params.toString();
        });

        function printReceipt(transactionId) {
            let receiptUrl = BASE + "/" + transactionId + "/receipt";

            let screenWidth = window.screen.width;
            let screenHeight = window.screen.height;

            let popupWidth = 1000;
            let popupHeight = 600;

            let left = (screenWidth - popupWidth) / 2;
            let top = (screenHeight - popupHeight) / 2;

            let printWindow = window.open(
                receiptUrl,
                "_blank",
                `width=${popupWidth},height=${popupHeight},top=${top},left=${left}`
            );

            if (printWindow) {
                printWindow.focus();
            } else {
                alert("Izinkan popup di browser untuk mencetak struk.");
            }
        }

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

        function deleteUsers(id) {
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
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var topCustomers = {!! json_encode($topCustomers->pluck('customer_name')) !!};
            var totalSpent = {!! json_encode($topCustomers->pluck('total_spent')) !!};

            var customerChart = new ApexCharts(document.getElementById('chart-top-customer'), {
                chart: {
                    type: "bar",
                    height: 300,
                    toolbar: { show: false }
                },
                series: [{ name: "Total Belanja", data: totalSpent }],
                xaxis: {
                    categories: topCustomers,
                    labels: { rotate: -45 },
                    tickPlacement: 'on',
                    scrollbar: { enabled: true },
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return value;
                        }
                    }
                },
                colors: ['#FF5733'],
                legend: { position: 'bottom' }
            });
            customerChart.render();
        });
    </script>
@endpush