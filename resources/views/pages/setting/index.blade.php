@extends('layouts.app')

@section('title')
    Setting
@endsection

@push('css')
    
@endpush

@section('content')
    @include('components.alert.success')
    @include('components.alert.warning')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('usaha.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mb-3">
                    <label for="" class="mb-2">Nama Usaha</label>
                    <input value="{{ $usaha->name ?? "" }}" type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror">
                    @error('name')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="" class="mb-2">Alamat Usaha</label>
                    <input value="{{ $usaha->address ?? "" }}" type="text" name="address" id="address" class="form-control @error('address') is-invalid @enderror">
                    @error('address')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="" class="mb-2">Nama Printer Thermal</label>
                    <input value="{{ $usaha->name_of_thermal ?? "" }}" type="text" name="name_of_thermal" id="name_of_thermal" class="form-control @error('name_of_thermal') is-invalid @enderror">
                    @error('name_of_thermal')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="" class="mb-2">Ucapan Terima Kasih</label>
                    <input value="{{ $usaha->footer ?? "" }}" type="text" name="footer" id="footer" class="form-control @error('footer') is-invalid @enderror">
                    @error('footer')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3">
                    <label for="" class="mb-2">Logo Usaha</label>
                    <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror">
                    @error('image')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary float-end">Simpan</button>
            </form>
        </div>
    </div>
@endsection

@push('js')
    
@endpush