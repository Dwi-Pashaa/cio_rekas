@extends('layouts.app')

@section('title')
    Grafik Transaksi & Pendapatan
@endsection

@push('css')
    
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-lg-4 col-md-4 col-sm-12 mb-2">
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
@endsection

@push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var topCustomers = {!! json_encode($topCustomers->pluck('customer_name')) !!};
            var totalSpent = {!! json_encode($topCustomers->pluck('total_spent')) !!};

            var customerChart = new ApexCharts(document.getElementById('chart-top-customer'), {
                chart: {
                    type: "bar",
                    height: "100%",
                    toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true
                    }
                },
                series: [{ name: "Total Belanja", data: totalSpent }],
                xaxis: {
                    categories: topCustomers
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