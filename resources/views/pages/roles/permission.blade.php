@extends('layouts.app')

@section('title')
    Level {{ $role->name }}
@endsection

@push('css')
    
@endpush

@section('content') 
    @include('components.alert.success')
    <div class="card">
        <div class="card-header">
            <b>Atur Aksess Menu Untuk Level {{ $role->name }}</b>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.savePermission', ['id' => $role->id]) }}" method="POST">
                @csrf
                @method("PUT")
                <div class="row">
                    @foreach ($permissions as $item)
                        <div class="col-lg-3 mb-2 mt-2">
                            <label class="form-check form-switch form-switch-3">
                                <input class="form-check-input" name="permissions[]" value="{{ $item->name }}" type="checkbox" {{ $role->hasPermissionTo($item->name) ? 'checked' : '' }}>
                                <span class="form-check-label">{{ $item->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <hr>
                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
    </div>
@endsection

@push('js')
    
@endpush