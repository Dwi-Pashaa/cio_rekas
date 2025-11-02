@extends('layouts.app')

@section('title')
    Rekap Keuangan
@endsection

@push('css')
    
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex">
                        <h3 class="card-title">Grafik Pendaptan Perbulan</h3>
                    </div>
                    <div id="chart-income"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex">
                        <h3 class="card-title">Grafik Barang Terjual Perbulan</h3>
                    </div>
                    <div id="chart-products-sold"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="col-lg-5 col-md-4 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="" class="mb-2">Tanggal Mulai</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-4 col-sm-12">
                        <div class="form-group mb-3">
                            <label for="" class="mb-2">Tanggal Selesai</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-2 col-sm-12">
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary w-100 mt-4">Cetak</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        @can('download excel')
            @if (!empty(request('start_date')) && !empty(request('end_date')))
                <div class="card-body">
                    <a href="{{ route('keuangan.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="btn btn-success">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                        Download Excel
                    </a>
                </div>
            @endif
        @endcan
        <div class="table-responsive">
            <table class="table card-table table-vcenter text-nowrap datatable">
                <thead>
                    <tr>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transaction as $item)
                        <tr>
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
                                    {{ $item->customer->type->name ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <a href="#" class="text-reset" tabindex="-1">
                                    {{ $item->customer->status->name ?? '-' }}
                                </a>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i:s') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Tidak Ada Data</td>
                        </tr>
                    @endforelse
                </tbody>
                @if (!empty(request('start_date')) && !empty(request('end_date')))
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-end">Total</th>
                            <th colspan="4" class="text-center">Rp. {{ number_format($transaction->sum('total')) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                type: "bar",
                fontFamily: 'inherit',
                height: 300,
                parentHeightOffset: 0,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: true
                },
            },
            fill: {
                opacity: 1,
            },
            stroke: {
                width: 2,
                lineCap: "round",
                curve: "smooth",
            },
            series: [{
                name: "Pendapatan",
                data: {!! json_encode($incomeData) !!}
            }],
            tooltip: {
                theme: 'dark'
            },
            grid: {
                padding: {
                    top: -20,
                    right: 0,
                    left: -4,
                    bottom: -4
                },
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
            },
            xaxis: {
                categories: {!! json_encode($months) !!},
                labels: {
                    padding: 0,
                },
                tooltip: {
                    enabled: false
                },
            },
            yaxis: {
                labels: {
                    padding: 4,
                    formatter: function (value) {
                        return "Rp " + new Intl.NumberFormat().format(value);
                    }
                },
            },
            colors: ['#FF5733'],
            legend: {
                show: true,
                position: 'bottom',
                offsetY: 12,
                markers: {
                    width: 10,
                    height: 10,
                    radius: 100,
                },
                itemMargin: {
                    horizontal: 8,
                    vertical: 8
                },
            },
        };

        var chart = new ApexCharts(document.getElementById('chart-income'), options);
        chart.render();
    });

    document.addEventListener("DOMContentLoaded", function () {
        window.ApexCharts && (new ApexCharts(document.getElementById('chart-products-sold'), {
            chart: {
                type: "line",
                fontFamily: 'inherit',
                height: 300,
                parentHeightOffset: 0,
                toolbar: { show: false },
                animations: { enabled: false },
            },
            fill: { opacity: 1 },
            stroke: { width: 2, lineCap: "round", curve: "smooth" },
            series: [
                @foreach($productNames as $productName)
                {
                    name: "{{ $productName }}",
                    data: {!! json_encode(array_values($productsPerMonth[$productName])) !!}
                },
                @endforeach
            ],
            tooltip: { theme: 'dark' },
            grid: {
                padding: { top: -20, right: 0, left: -4, bottom: -4 },
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
            },
            xaxis: {
                categories: {!! json_encode($months) !!},
                labels: { padding: 0 },
                tooltip: { enabled: false }
            },
            yaxis: { labels: { padding: 4 } },
            legend: {
                show: true,
                position: 'bottom',
                offsetY: 12,
                markers: { width: 10, height: 10, radius: 100 },
                itemMargin: { horizontal: 8, vertical: 8 },
            },
        })).render();
    });
</script>
@endpush