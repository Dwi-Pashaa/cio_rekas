@extends('layouts.app')

@section('title')
    Dashboard
@endsection

@push('css')
    
@endpush

@section('content')
    <div class="alert alert-primary">
        <b>Selamat Datang Di {{ config('app.name') }} {{ Auth::user()->name }}</b>
    </div>
@endsection

@push('js')
    
@endpush