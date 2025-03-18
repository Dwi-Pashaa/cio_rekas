@extends('layouts.app')

@section('title')
    Rekap Keuangan
@endsection

@push('css')
    
@endpush

@section('content')
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
        @if (!empty(request('start_date')) && !empty(request('end_date')))
            <div class="card-body">
                <a href="{{ route('keuangan.export') }}" class="btn btn-success">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-spreadsheet"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M8 11h8v7h-8z" /><path d="M8 15h8" /><path d="M11 11v7" /></svg>
                    Download Excel
                </a>
            </div>
        @endif
        <div class="table-responsive-lg">
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
    
@endpush