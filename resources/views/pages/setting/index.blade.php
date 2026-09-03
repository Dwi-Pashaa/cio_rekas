@extends('layouts.app')

@section('title', 'Profil Toko & Pengaturan')
@section('pretitle', 'Pengaturan')

@section('content')
    @include('components.alert.success')
    @include('components.alert.warning')

    <form action="{{ route('usaha.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" id="id" value="{{ $usaha->id ?? '' }}">

        <div class="row g-4">
            {{-- KOLOM KIRI: Profil Usaha & Struk Printer --}}
            <div class="col-lg-6 col-12 space-y-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <x-icons.settings class="w-5 h-5 text-primary" />
                            <h5 class="card-title fw-bold text-dark m-0">Profil Toko & Printer Struk</h5>
                        </div>
                    </div>
                    <div class="card-body p-4 space-y-3">
                        <div class="form-group mb-3">
                            <label for="name" class="form-label fw-bold text-muted small text-uppercase">Nama Usaha / Toko <span class="text-danger">*</span></label>
                            <input value="{{ old('name', $usaha->name ?? '') }}" type="text" name="name" id="name" class="form-control form-control-md rounded-2 @error('name') is-invalid @enderror" placeholder="Contoh: CIO REKAS POS">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="address" class="form-label fw-bold text-muted small text-uppercase">Alamat Usaha <span class="text-danger">*</span></label>
                            <input value="{{ old('address', $usaha->address ?? '') }}" type="text" name="address" id="address" class="form-control form-control-md rounded-2 @error('address') is-invalid @enderror" placeholder="Alamat lengkap toko / kantor">
                            @error('address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="name_of_thermal" class="form-label fw-bold text-muted small text-uppercase">Nama Printer Thermal <span class="text-danger">*</span></label>
                            <input value="{{ old('name_of_thermal', $usaha->name_of_thermal ?? '') }}" type="text" name="name_of_thermal" id="name_of_thermal" class="form-control form-control-md rounded-2 @error('name_of_thermal') is-invalid @enderror" placeholder="Contoh: POS-58 atau Printer-Thermal">
                            @error('name_of_thermal')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="footer" class="form-label fw-bold text-muted small text-uppercase">Ucapan Terima Kasih / Footer Struk <span class="text-danger">*</span></label>
                            <input value="{{ old('footer', $usaha->footer ?? '') }}" type="text" name="footer" id="footer" class="form-control form-control-md rounded-2 @error('footer') is-invalid @enderror" placeholder="Contoh: Terima kasih atas kunjungan Anda!">
                            @error('footer')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="image" class="form-label fw-bold text-muted small text-uppercase">Logo Usaha</label>
                            @if(!empty($usaha->image))
                                <div class="mb-2">
                                    <img src="{{ asset($usaha->image) }}" alt="Logo Toko" class="img-thumbnail rounded-2 shadow-xs" style="max-height: 60px; object-fit: contain;">
                                </div>
                            @endif
                            <input type="file" name="image" id="image" class="form-control form-control-md rounded-2 @error('image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
                            <span class="text-muted small">Format didukung: PNG, JPG, WEBP.</span>
                            @error('image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- KOLOM KANAN: Pengaturan Notifikasi (WhatsApp Mekari & Email SMTP) --}}
            <div class="col-lg-6 col-12 space-y-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <x-icons.receipt class="w-5 h-5 text-blue-600" />
                                <h5 class="card-title fw-bold text-dark m-0">Notifikasi Transaksi Otomatis</h5>
                            </div>
                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill text-xs fw-semibold">
                                WhatsApp & Email
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4 space-y-4">
                        {{-- Toggle Switches Box --}}
                        <div class="p-3 bg-light rounded-3 border border-slate-200 space-y-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark fs-6">Notifikasi WhatsApp (Mekari Qontak)</div>
                                    <div class="text-muted small">Kirim pesan WhatsApp otomatis ke nomor Agent dan Admin.</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="enable_wa_notification" id="enable_wa_notification" value="1" {{ old('enable_wa_notification', $usaha->enable_wa_notification ?? false) ? 'checked' : '' }} style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                </div>
                            </div>

                            <hr class="my-2 border-slate-200">

                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-dark fs-6">Notifikasi Email (SMTP)</div>
                                    <div class="text-muted small">Kirim struk invoice resmi ke alamat email Agent saja.</div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" name="enable_email_notification" id="enable_email_notification" value="1" {{ old('enable_email_notification', $usaha->enable_email_notification ?? false) ? 'checked' : '' }} style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                </div>
                            </div>
                        </div>

                        {{-- Nomor WA Admin --}}
                        <div class="form-group">
                            <label for="admin_wa_number" class="form-label fw-bold text-muted small text-uppercase">Nomor WhatsApp Admin (Tembusan Transaksi)</label>
                            <input value="{{ old('admin_wa_number', $usaha->admin_wa_number ?? '') }}" type="text" name="admin_wa_number" id="admin_wa_number" class="form-control form-control-md rounded-2" placeholder="Contoh: 081234567890">
                            <span class="text-muted small">Setiap kali ada transaksi baru, nomor admin ini akan menerima salinan notifikasi WhatsApp.</span>
                        </div>

                        {{-- Konfigurasi Mekari Qontak API --}}
                        <div class="border rounded-3 p-3 bg-white space-y-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle px-2 py-1 rounded fw-semibold text-xs">
                                    Mekari Qontak Configuration
                                </span>
                            </div>

                            <div class="form-group mb-2.5">
                                <label for="qontak_token" class="form-label fw-bold text-muted small text-uppercase">API Token Mekari Qontak</label>
                                <input value="{{ old('qontak_token', $usaha->qontak_token ?? '') }}" type="password" name="qontak_token" id="qontak_token" class="form-control form-control-sm rounded-2 font-monospace" placeholder="Bearer Token dari Mekari Qontak">
                            </div>

                            <div class="row g-2">
                                <div class="col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="qontak_channel_id" class="form-label fw-bold text-muted small text-uppercase">Channel Integration ID</label>
                                        <input value="{{ old('qontak_channel_id', $usaha->qontak_channel_id ?? '') }}" type="text" name="qontak_channel_id" id="qontak_channel_id" class="form-control form-control-sm rounded-2 font-monospace" placeholder="ID Channel WhatsApp">
                                    </div>
                                </div>
                                <div class="col-sm-6 col-12">
                                    <div class="form-group">
                                        <label for="qontak_template_id" class="form-label fw-bold text-muted small text-uppercase">Message Template ID</label>
                                        <input value="{{ old('qontak_template_id', $usaha->qontak_template_id ?? '') }}" type="text" name="qontak_template_id" id="qontak_template_id" class="form-control form-control-sm rounded-2 font-monospace" placeholder="ID Template Pesan">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Submit Card / Button --}}
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                        <x-icons.check class="w-5 h-5" />
                        <span>Simpan Semua Pengaturan</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection