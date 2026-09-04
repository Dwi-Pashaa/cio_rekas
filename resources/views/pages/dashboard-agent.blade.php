@extends('layouts.app')

@section('title', 'Dashboard Agent - Transaksi Mandiri')
@section('pretitle', 'Panel Kasir POS Mandiri')

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        <div class="d-flex align-items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Kasir Agent Aktif</span>
        </div>
    </div>
@endsection

@section('content')
@php
    $deliveryFee = floatval($usaha->delivery_fee ?? 0);
    $itemSubtotal = floatval(optional($customer)->total ?? 0);
@endphp

@if(!$customer)
    {{-- Alert jika profile agent belum tertaut --}}
    <div class="alert alert-warning border-0 rounded-4 p-4 shadow-sm">
        <div class="d-flex align-items-start gap-3">
            <div class="w-12 h-12 rounded-3 bg-amber-500 text-white d-flex align-items-center justify-center shrink-0 fs-3">
                <x-icons.shield class="w-6 h-6" />
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-1">Profil Data Agent Belum Terhubung</h4>
                <p class="text-muted mb-0">
                    Akun login Anda (<b>{{ Auth::user()->username }}</b>) belum ditautkan dengan data Master Agent atau paket barang Anda belum ditentukan. Silakan hubungi Administrator untuk mengatur paket barang dan ketentuan pembelian Anda.
                </p>
            </div>
        </div>
    </div>
@else
    {{-- Banner Sambutan Agent --}}
    <div class="rounded-4 p-4 text-white shadow-sm position-relative overflow-hidden mb-4" 
         style="background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 45%, #2563EB 100%);">
        <div class="position-absolute" style="top: -40px; right: -40px; width: 200px; height: 200px; border-radius: 50%; background: radial-gradient(circle, rgba(96, 165, 250, 0.25) 0%, rgba(255,255,255,0) 70%); pointer-events: none;"></div>
        
        <div class="position-relative z-1 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" 
                     style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2);">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs fw-bold text-white">POS Mandiri Agent</span>
                    <span class="text-white-50">&bull;</span>
                    <span class="text-xs text-blue-200">Sistem Pemesanan Otomatis</span>
                </div>
                <h2 class="text-white fw-extrabold tracking-tight fs-2 mb-1">
                    Selamat Datang, {{ $customer->name }}! 👋
                </h2>
                <p class="text-blue-100 fs-6 max-w-2xl m-0 opacity-90">
                    Pilih cabang penyedia barang, opsi penyerahan (ambil sendiri / diantar kurir), dan metode pembayaran untuk menyelesaikan pesanan.
                </p>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-blue-900 px-3 py-2 rounded-3 font-monospace fw-bold fs-7 shadow-xs">
                    SN: {{ $customer->code }}
                </span>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- KOLOM KIRI: Identitas Profil & Ketentuan Paket Pembelian Otomatis --}}
        <div class="col-lg-5 col-12 space-y-3">
            
            {{-- 1. Kartu Identitas Agent --}}
            <div class="card border-0 shadow-sm rounded-2xl">
                <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                    <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                        <x-icons.users class="w-4 h-4 text-blue-600" />
                        Profil Agent Anda
                    </h4>
                    @php
                        $statusName = strtolower(optional($customer->status)->name ?? 'aktif');
                        $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                        if (str_contains($statusName, 'blokir') || str_contains($statusName, 'nonaktif')) {
                            $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                        }
                    @endphp
                    <span class="badge {{ $badgeClass }} border px-2.5 py-1 rounded-pill fw-semibold">
                        {{ optional($customer->status)->name ?? 'Aktif' }}
                    </span>
                </div>
                <div class="card-body p-4 space-y-3">
                    <div class="d-flex align-items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                        <div class="w-12 h-12 rounded-xl bg-blue-600 text-white fw-bold d-flex align-items-center justify-center shrink-0 fs-5 shadow-sm">
                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="fw-bold text-slate-900 m-0 text-truncate fs-5">{{ $customer->name }}</h4>
                            <div class="d-flex align-items-center gap-2 mt-0.5">
                                <span class="badge bg-light text-primary border font-monospace fw-bold fs-8">SN: {{ $customer->code }}</span>
                                @if($customer->nik)
                                    <span class="badge bg-light text-slate-700 border font-monospace fs-8">NIK: {{ $customer->nik }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 text-xs">
                        <div class="col-sm-6 col-12">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                                <span class="text-slate-400 d-block mb-0.5">No. Telepon / WA:</span>
                                <span class="text-slate-800 fw-bold fs-7">{{ $customer->telp ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6 col-12">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 h-100">
                                <span class="text-slate-400 d-block mb-0.5">Email Akun:</span>
                                <span class="text-slate-800 fw-bold fs-7 text-truncate d-block" title="{{ $customer->email }}">{{ $customer->email ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 d-block mb-0.5">Alamat Pengiriman (Jika Diantar Kurir):</span>
                                <span class="text-slate-800 fw-semibold fs-7 d-block" id="display-agent-address">{{ $customer->address ?: '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Rincian Paket Barang yang Ditugaskan --}}
            <div class="card border-0 shadow-sm rounded-2xl">
                <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                    <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                        <x-icons.package class="w-4 h-4 text-blue-600" />
                        Paket Barang Pembelian
                    </h4>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-pill text-xs">Terkunci Otomatis</span>
                </div>
                <div class="card-body p-4 space-y-3">
                    @if($customer->product)
                        <div class="d-flex align-items-center justify-content-between p-3 rounded-xl bg-slate-50 border border-slate-100 flex-wrap gap-2">
                            <div>
                                <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Nama Paket Barang:</span>
                                <span class="fw-bold text-slate-900 fs-6">{{ $customer->product->name }}</span>
                                <div class="text-xs text-slate-500 font-monospace mt-0.5">Kode: {{ $customer->product->code }}</div>
                            </div>
                            <div class="text-end">
                                <span class="text-xs text-slate-400 fw-semibold text-uppercase d-block">Kuota Pembelian (Limit):</span>
                                <span class="fw-bold text-blue-600 fs-4">{{ $customer->limit }} Unit</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between text-xs px-1 text-slate-600">
                            <span>Harga Satuan:</span>
                            <span class="fw-bold text-slate-800 fs-7">Rp {{ number_format($customer->amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-xs px-1 text-slate-600 border-top pt-2">
                            <span>Subtotal Paket Barang:</span>
                            <span class="fw-bold text-slate-900 fs-6">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-xs">
                            Paket barang untuk akun Anda belum diatur oleh Administrator.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- KOLOM KANAN: Form Pemesanan & Alur Checkout Mandiri --}}
        <div class="col-lg-7 col-12 space-y-3">
            
            <div class="card border-0 shadow-sm rounded-2xl">
                <div class="card-header bg-transparent border-bottom border-slate-100 py-3 d-flex align-items-center justify-content-between">
                    <h4 class="card-title text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center gap-1.5 m-0">
                        <x-icons.cart class="w-4 h-4 text-blue-600" />
                        Detail Pemesanan Mandiri
                    </h4>
                    <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-1 rounded-pill fw-semibold fs-8">
                        Langkah Transaksi
                    </span>
                </div>

                <div class="card-body p-4 space-y-4">
                    
                    {{-- LANGKAH 1: PILIH CABANG PENYEDIA --}}
                    <div>
                        <label class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-flex align-items-center justify-content-between mb-2">
                            <span>1. Pilih Cabang Penyedia Barang <span class="text-danger">*</span></span>
                            <span class="text-muted fw-normal lowercase" style="font-size: 0.72rem;">stok terupdate realtime</span>
                        </label>
                        
                        <div class="row g-2">
                            @foreach($branches as $br)
                                @php
                                    $stk = $branchStockMap[$br->id] ?? 0;
                                    $isDefault = ($br->id == $defaultBranchId);
                                    $isOutOfStock = ($stk < ($customer->limit ?? 1));
                                @endphp
                                <div class="col-md-6 col-12">
                                    <div class="branch-card w-100 p-3 rounded-xl border border-slate-200 d-flex align-items-start justify-content-between gap-2 cursor-pointer {{ $isDefault ? 'selected-branch' : '' }} {{ $isOutOfStock ? 'opacity-60' : '' }}" 
                                         id="branch_card_{{ $br->id }}" 
                                         data-branch-id="{{ $br->id }}"
                                         data-stock="{{ $stk }}"
                                         onclick="selectBranch('{{ $br->id }}', {{ $stk }})">
                                        <div class="d-flex align-items-center gap-2.5 flex-grow-1">
                                            <input type="radio" name="selected_branch_id" value="{{ $br->id }}" data-stock="{{ $stk }}" id="radio_branch_{{ $br->id }}" {{ $isDefault ? 'checked' : '' }} class="form-check-input m-0 cursor-pointer" onchange="selectBranch('{{ $br->id }}', {{ $stk }})">
                                            <label for="radio_branch_{{ $br->id }}" class="cursor-pointer m-0 flex-grow-1" onclick="selectBranch('{{ $br->id }}', {{ $stk }})">
                                                <div class="fw-bold text-slate-900 fs-7">{{ $br->name }}</div>
                                                <div class="text-muted" style="font-size: 0.72rem;">
                                                    Sisa Stok: <b class="{{ $stk > 0 ? 'text-blue-600' : 'text-danger' }}">{{ number_format($stk, 0, ',', '.') }} unit</b>
                                                </div>
                                            </label>
                                        </div>
                                        @if($isOutOfStock)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 rounded text-xs" style="font-size: 0.65rem;">Stok Kurang</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-1.5 py-0.5 rounded text-xs" style="font-size: 0.65rem;">Tersedia</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="stock-warning" class="text-danger small mt-1.5 d-none font-semibold">
                            ⚠️ Stok di cabang ini tidak mencukupi untuk jumlah pembelian ({{ $customer->limit }} unit). Silakan pilih cabang lain.
                        </div>
                    </div>

                    {{-- LANGKAH 2: PILIH METODE PENYERAHAN BARANG (AMBIL SENDIRI VS DIANTAR KURIR) --}}
                    <div>
                        <label class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-block mb-2">
                            2. Pilih Opsi Penyerahan Barang <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            {{-- Opsi 1: Ambil Sendiri --}}
                            <div class="col-md-6 col-12">
                                <div class="delivery-card w-100 p-3 rounded-xl border border-slate-200 d-flex align-items-center gap-2.5 cursor-pointer selected-delivery" 
                                     id="card_delivery_pickup" 
                                     data-type="pickup"
                                     onclick="selectDeliveryOption('pickup')">
                                    <input type="radio" name="delivery_type" value="pickup" id="radio_pickup" checked class="form-check-input m-0 cursor-pointer" onchange="selectDeliveryOption('pickup')">
                                    <label for="radio_pickup" class="cursor-pointer m-0 flex-grow-1" onclick="selectDeliveryOption('pickup')">
                                        <div class="fw-bold text-slate-900 fs-7 d-flex align-items-center gap-1.5">
                                            <x-icons.branch class="w-4 h-4 text-blue-600" />
                                            Ambil di Cabang
                                        </div>
                                        <div class="text-xs text-emerald-600 fw-bold mt-0.5" style="font-size: 0.72rem;">
                                            Gratis (Rp 0)
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Opsi 2: Diantar Kurir --}}
                            <div class="col-md-6 col-12">
                                <div class="delivery-card w-100 p-3 rounded-xl border border-slate-200 d-flex align-items-center gap-2.5 cursor-pointer" 
                                     id="card_delivery_courier" 
                                     data-type="delivery"
                                     onclick="selectDeliveryOption('delivery')">
                                    <input type="radio" name="delivery_type" value="delivery" id="radio_courier" class="form-check-input m-0 cursor-pointer" onchange="selectDeliveryOption('delivery')">
                                    <label for="radio_courier" class="cursor-pointer m-0 flex-grow-1" onclick="selectDeliveryOption('delivery')">
                                        <div class="fw-bold text-slate-900 fs-7 d-flex align-items-center gap-1.5">
                                            <x-icons.truck class="w-4 h-4 text-indigo-600" />
                                            Diantar Kurir
                                        </div>
                                        <div class="text-xs text-indigo-600 fw-bold mt-0.5" style="font-size: 0.72rem;">
                                            + Rp {{ number_format($deliveryFee, 0, ',', '.') }} (Jasa Antar)
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- LANGKAH 3: PILIH METODE PEMBAYARAN (CASH VS TRANSFER XENDIT) --}}
                    <div>
                        <label class="text-xs fw-bold text-slate-700 text-uppercase tracking-wider d-block mb-2">
                            3. Pilih Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            @if(in_array('cash', $allowedMethods))
                                <div class="{{ in_array('transfer', $allowedMethods) ? 'col-md-6 col-12' : 'col-12' }}">
                                    <div class="payment-method-card w-100 p-3 rounded-xl border border-slate-200 d-flex align-items-start gap-2.5 cursor-pointer selected-method" 
                                         id="card_method_cash" 
                                         data-method="cash"
                                         onclick="selectPaymentMethod('cash')">
                                        <input type="radio" name="payment_method_type" value="cash" id="radio_cash" checked class="form-check-input m-0 mt-0.5 cursor-pointer" onchange="selectPaymentMethod('cash')">
                                        <label for="radio_cash" class="cursor-pointer m-0 flex-grow-1" onclick="selectPaymentMethod('cash')">
                                            <div class="fw-bold text-dark fs-7 d-flex align-items-center gap-1">
                                                <x-icons.cash class="w-4 h-4 text-emerald-600" />
                                                Cash (Tunai)
                                            </div>
                                            <div class="text-muted small mt-0.5" style="font-size: 0.7rem;" id="cash-method-hint">
                                                Uang diserahkan ke Kasir saat ambil barang di Cabang.
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            @if(in_array('transfer', $allowedMethods))
                                <div class="{{ in_array('cash', $allowedMethods) ? 'col-md-6 col-12' : 'col-12' }}">
                                    <div class="payment-method-card w-100 p-3 rounded-xl border border-slate-200 d-flex align-items-start gap-2.5 cursor-pointer {{ !in_array('cash', $allowedMethods) ? 'selected-method' : '' }}" 
                                         id="card_method_transfer" 
                                         data-method="xendit"
                                         onclick="selectPaymentMethod('xendit')">
                                        <input type="radio" name="payment_method_type" value="xendit" id="radio_transfer" {{ !in_array('cash', $allowedMethods) ? 'checked' : '' }} class="form-check-input m-0 mt-0.5 cursor-pointer" onchange="selectPaymentMethod('xendit')">
                                        <label for="radio_transfer" class="cursor-pointer m-0 flex-grow-1" onclick="selectPaymentMethod('xendit')">
                                            <div class="fw-bold text-dark fs-7 d-flex align-items-center gap-1">
                                                <x-icons.receipt class="w-4 h-4 text-indigo-600" />
                                                Transfer (Xendit Gateway)
                                            </div>
                                            <div class="text-muted small mt-0.5" style="font-size: 0.7rem;">
                                                QRIS, Virtual Account BCA/BRI/BNI/Mandiri & E-Wallet.
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- RINGKASAN BIAYA & TOTAL PEMBAYARAN --}}
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50 to-slate-50 border border-blue-200 space-y-2.5">
                        <div class="d-flex align-items-center justify-content-between text-xs text-slate-600">
                            <span>Subtotal Barang ({{ $customer->limit }} Unit):</span>
                            <span class="fw-bold text-slate-800">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-between text-xs text-slate-600" id="row-delivery-fee">
                            <span id="label-delivery-fee">Biaya Pengambilan:</span>
                            <span class="fw-bold text-emerald-600" id="val-delivery-fee">Gratis (Rp 0)</span>
                        </div>

                        <hr class="my-2 border-blue-200">

                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-xs fw-extrabold text-blue-900 text-uppercase tracking-wider d-block">TOTAL PEMBAYARAN</span>
                                <span class="text-muted text-xs" id="summary-payment-desc">Bayar Tunai</span>
                            </div>
                            <div class="text-end">
                                <span class="fs-1 fw-extrabold text-blue-700 font-monospace" id="display-grand-total">
                                    Rp {{ number_format($itemSubtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL CHECKOUT / PROSES PESANAN --}}
                    <button 
                        type="button" 
                        id="btn-checkout" 
                        class="btn btn-primary btn-lg w-100 py-3 rounded-xl fw-bold text-white d-flex align-items-center justify-content-center gap-2 shadow-sm"
                        style="background: linear-gradient(135deg, #1E40AF 0%, #2563EB 100%); font-size: 1.05rem;"
                        onclick="processAgentOrder()"
                    >
                        <x-icons.cart class="w-5 h-5" />
                        <span id="btn-checkout-text">Buat Pesanan & Bayar Cash</span>
                    </button>

                    <div class="text-center">
                        <small class="text-muted text-xs d-block">
                            🔔 Notifikasi WhatsApp otomatis akan dikirimkan ke <b>Kasir Cabang</b> yang dipilih untuk mempersiapkan pesanan Anda.
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endif

{{-- MODAL SUKSES TRANSAKSI CASH --}}
<div class="modal fade" id="modal-success-receipt" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-success text-white border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="w-8 h-8 rounded-circle bg-white text-success d-flex align-items-center justify-center font-bold">
                        <x-icons.check class="w-5 h-5" />
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0">Pesanan Berhasil Dibuat!</h6>
                        <small class="text-white-50" style="font-size: 0.72rem;">Kasir cabang telah menerima notifikasi</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.reload()"></button>
            </div>
            
            <div class="modal-body p-4 bg-light text-center">
                {{-- Thermal Receipt Paper Container --}}
                <div id="receipt-paper" class="bg-white p-4 rounded-3 border shadow-xs text-start font-monospace text-xs" style="color: #000; line-height: 1.4;">
                    <div class="text-center pb-2 border-bottom border-dashed mb-2">
                        <div class="fw-bold fs-6">{{ $usaha->name ?? 'CIO REKAS' }}</div>
                        <div class="small text-muted">{{ $usaha->address ?? 'Pusat Distribusi' }}</div>
                        <div class="small text-muted">{{ $usaha->phone ?? '' }}</div>
                    </div>
                    
                    <div class="d-flex justify-content-between pb-1">
                        <span>Invoice:</span>
                        <span class="fw-bold" id="rcpt-invoice">TRX-00000</span>
                    </div>
                    <div class="d-flex justify-content-between pb-1">
                        <span>Cabang:</span>
                        <span class="fw-bold" id="rcpt-branch">-</span>
                    </div>
                    <div class="d-flex justify-content-between pb-1">
                        <span>Agent:</span>
                        <span class="fw-bold" id="rcpt-customer">{{ $customer->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between pb-1">
                        <span>Opsi Penyerahan:</span>
                        <span class="fw-bold text-primary" id="rcpt-delivery">-</span>
                    </div>
                    <div class="d-flex justify-content-between pb-2 border-bottom border-dashed mb-2">
                        <span>Metode Bayar:</span>
                        <span class="fw-bold" id="rcpt-method">CASH</span>
                    </div>

                    <div class="pb-2 border-bottom border-dashed mb-2">
                        <div class="fw-bold" id="rcpt-product">{{ $customer->product->name ?? '-' }}</div>
                        <div class="d-flex justify-content-between text-muted">
                            <span id="rcpt-qty-price">{{ $customer->limit ?? 1 }} x Rp {{ number_format($customer->amount ?? 0, 0, ',', '.') }}</span>
                            <span class="text-dark fw-bold" id="rcpt-subtotal">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between text-muted mt-1" id="rcpt-row-fee">
                            <span>Jasa Antar Kurir:</span>
                            <span class="text-dark fw-bold" id="rcpt-fee">Rp 0</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-6 pt-1">
                        <span>TOTAL BAYAR:</span>
                        <span id="rcpt-total">Rp {{ number_format($itemSubtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="text-center pt-3 text-muted" style="font-size: 0.68rem;">
                        <div>Pesanan sedang dipersiapkan oleh kasir cabang.</div>
                        <div class="font-monospace">Simpan bukti transaksi ini</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3 rounded-2" data-bs-dismiss="modal" onclick="window.location.reload()">
                    Tutup
                </button>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btn-print-receipt" target="_blank" class="btn btn-primary px-3 rounded-2 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs">
                        <x-icons.printer class="w-4 h-4" />
                        <span>Cetak Struk Thermal</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .branch-card, .delivery-card, .payment-method-card {
        transition: all 0.2s ease;
        background-color: #FFFFFF;
    }
    .branch-card:hover, .delivery-card:hover, .payment-method-card:hover {
        border-color: #3B82F6 !important;
        background-color: #F8FAFC !important;
    }
    .branch-card.selected-branch, .delivery-card.selected-delivery, .payment-method-card.selected-method {
        border-color: #2563EB !important;
        background-color: #EFF6FF !important;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@push('js')
<script>
    // Konfigurasi Nilai & State
    window.ITEM_SUBTOTAL = Number("{{ (int) ($itemSubtotal ?? 0) }}") || 0;
    window.DELIVERY_FEE = Number("{{ (int) ($deliveryFee ?? 0) }}") || 0;
    window.CUSTOMER_ID = Number("{{ (int) ($customer->id ?? 0) }}") || 0;
    window.PRODUCT_ID = Number("{{ (int) ($customer->products_id ?? 0) }}") || 0;
    window.LIMIT_QTY = Number("{{ (int) ($customer->limit ?? 1) }}") || 1;
    window.STORE_URL = "{{ route('transaksi.store') }}";
    window.PRODUCT_NAME = @json(($customer->product->name ?? 'Paket Barang') . ' (' . ($customer->limit ?? 1) . ' Unit)');
    window.RECEIPT_URL_PREFIX = @json(url('/transaction'));

    window.currentBranchId = "{{ $defaultBranchId }}";
    window.currentDeliveryType = 'pickup';
    window.currentPaymentMethod = "{{ in_array('cash', $allowedMethods) ? 'cash' : 'xendit' }}";

    // Format Rupiah Standar
    window.formatRupiah = function(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    };

    // Fungsi Update Grand Total Realtime
    window.updateGrandTotal = function() {
        var fee = (window.currentDeliveryType === 'delivery') ? window.DELIVERY_FEE : 0;
        var grandTotal = window.ITEM_SUBTOTAL + fee;
        var displayEl = document.getElementById('display-grand-total');
        if (displayEl) {
            displayEl.innerText = "Rp " + window.formatRupiah(grandTotal);
        }
    };

    // Fungsi Pilih Opsi Penyerahan Barang (Ambil Sendiri vs Diantar Kurir)
    window.selectDeliveryOption = function(type) {
        window.currentDeliveryType = type;

        var pickupCard = document.getElementById('card_delivery_pickup');
        var courierCard = document.getElementById('card_delivery_courier');
        var radioPickup = document.getElementById('radio_pickup');
        var radioCourier = document.getElementById('radio_courier');
        var labelFee = document.getElementById('label-delivery-fee');
        var valFee = document.getElementById('val-delivery-fee');
        var hintEl = document.getElementById('cash-method-hint');

        if (type === 'delivery') {
            if (courierCard) courierCard.classList.add('selected-delivery');
            if (pickupCard) pickupCard.classList.remove('selected-delivery');
            if (radioCourier) radioCourier.checked = true;
            if (labelFee) labelFee.innerText = "Biaya Jasa Antar Kurir:";
            if (valFee) {
                valFee.innerText = "+ Rp " + window.formatRupiah(window.DELIVERY_FEE);
                valFee.className = "fw-bold text-indigo-600";
            }
            if (hintEl) hintEl.innerText = "Uang diserahkan langsung ke Kurir saat barang tiba (COD).";
        } else {
            if (pickupCard) pickupCard.classList.add('selected-delivery');
            if (courierCard) courierCard.classList.remove('selected-delivery');
            if (radioPickup) radioPickup.checked = true;
            if (labelFee) labelFee.innerText = "Biaya Pengambilan:";
            if (valFee) {
                valFee.innerText = "Gratis (Rp 0)";
                valFee.className = "fw-bold text-emerald-600";
            }
            if (hintEl) hintEl.innerText = "Uang diserahkan ke Kasir saat ambil barang di Cabang.";
        }

        window.updateGrandTotal();
    };

    // Fungsi Pilih Cabang
    window.selectBranch = function(branchId, stockCount) {
        window.currentBranchId = branchId;
        
        var branchCards = document.querySelectorAll('.branch-card');
        for (var i = 0; i < branchCards.length; i++) {
            branchCards[i].classList.remove('selected-branch');
        }
        var activeCard = document.getElementById('branch_card_' + branchId);
        if (activeCard) activeCard.classList.add('selected-branch');
        
        var radio = document.getElementById('radio_branch_' + branchId);
        if (radio) radio.checked = true;

        var warningEl = document.getElementById('stock-warning');
        var btnCheckout = document.getElementById('btn-checkout');
        if (stockCount < window.LIMIT_QTY) {
            if (warningEl) warningEl.classList.remove('d-none');
            if (btnCheckout) btnCheckout.disabled = true;
        } else {
            if (warningEl) warningEl.classList.add('d-none');
            if (btnCheckout) btnCheckout.disabled = false;
        }
    };

    // Fungsi Pilih Metode Pembayaran
    window.selectPaymentMethod = function(method) {
        window.currentPaymentMethod = method;

        var cashCard = document.getElementById('card_method_cash');
        var transferCard = document.getElementById('card_method_transfer');
        var radioCash = document.getElementById('radio_cash');
        var radioTransfer = document.getElementById('radio_transfer');
        var btnText = document.getElementById('btn-checkout-text');
        var descText = document.getElementById('summary-payment-desc');

        if (cashCard) cashCard.classList.remove('selected-method');
        if (transferCard) transferCard.classList.remove('selected-method');

        if (method === 'cash') {
            if (cashCard) cashCard.classList.add('selected-method');
            if (radioCash) radioCash.checked = true;
            if (btnText) btnText.innerText = "Buat Pesanan & Bayar Cash";
            if (descText) descText.innerText = "Bayar Tunai";
        } else {
            if (transferCard) transferCard.classList.add('selected-method');
            if (radioTransfer) radioTransfer.checked = true;
            if (btnText) btnText.innerText = "Lanjut ke Pembayaran Xendit";
            if (descText) descText.innerText = "Transfer Online (Xendit)";
        }
    };

    // Proses Submit Pesanan
    window.processAgentOrder = function() {
        if (!window.currentBranchId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Pilih Cabang', text: 'Silakan pilih cabang penyedia barang terlebih dahulu.' });
            } else {
                alert('Silakan pilih cabang penyedia barang terlebih dahulu.');
            }
            return;
        }

        var fee = (window.currentDeliveryType === 'delivery') ? window.DELIVERY_FEE : 0;
        var grandTotal = window.ITEM_SUBTOTAL + fee;
        var deliveryLabel = (window.currentDeliveryType === 'delivery') ? '🛵 Diantar Kurir' : '🏪 Ambil Sendiri di Cabang';
        var paymentLabel = (window.currentPaymentMethod === 'cash') ? '💵 Cash (Tunai)' : '💳 Transfer (Xendit Gateway)';

        var executeOrder = function() {
            var btn = document.getElementById('btn-checkout');
            var btnText = document.getElementById('btn-checkout-text');
            if (btn) btn.disabled = true;
            if (btnText) btnText.innerText = 'Memproses pesanan...';

            $.ajax({
                url: window.STORE_URL,
                type: "POST",
                dataType: "json",
                data: {
                    _token: "{{ csrf_token() }}",
                    customers_id: window.CUSTOMER_ID,
                    products_id: window.PRODUCT_ID,
                    branch_id: window.currentBranchId,
                    qty: window.LIMIT_QTY,
                    delivery_type: window.currentDeliveryType,
                    delivery_fee: fee,
                    payment_method: window.currentPaymentMethod,
                    total: grandTotal,
                    payment: grandTotal
                },
                success: function(response) {
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.innerText = (window.currentPaymentMethod === 'cash' ? 'Buat Pesanan & Bayar Cash' : 'Lanjut ke Pembayaran Xendit');

                    if (response.code === 200) {
                        var trx = response.transaction;

                        if (window.currentPaymentMethod === 'xendit' || window.currentPaymentMethod === 'transfer') {
                            if (response.invoice_url) {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        title: "Membuka Halaman Pembayaran",
                                        text: "Anda akan diarahkan ke halaman pembayaran Xendit...",
                                        icon: "info",
                                        timer: 2000,
                                        timerProgressBar: true,
                                        showConfirmButton: false
                                    }).then(function() {
                                        window.location.href = response.invoice_url;
                                    });
                                } else {
                                    window.location.href = response.invoice_url;
                                }
                                return;
                            } else {
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal Membuka Xendit',
                                        text: 'Link pembayaran Xendit tidak dapat dibuat. Periksa izin API Key Xendit.'
                                    });
                                } else {
                                    alert('Link pembayaran Xendit tidak dapat dibuat. Periksa izin API Key Xendit.');
                                }
                                return;
                            }
                        }

                        // Tampilkan Struk untuk Cash
                        var selBranch = document.querySelector('.selected-branch .fw-bold');
                        var branchName = selBranch ? selBranch.innerText.trim() : 'Cabang';

                        var rInvoice = document.getElementById('rcpt-invoice');
                        var rBranch = document.getElementById('rcpt-branch');
                        var rDelivery = document.getElementById('rcpt-delivery');
                        var rMethod = document.getElementById('rcpt-method');
                        var rFee = document.getElementById('rcpt-fee');
                        var rTotal = document.getElementById('rcpt-total');
                        var btnPrint = document.getElementById('btn-print-receipt');

                        if (rInvoice) rInvoice.innerText = trx.code || ('TRX-' + trx.id);
                        if (rBranch) rBranch.innerText = branchName;
                        if (rDelivery) rDelivery.innerText = (window.currentDeliveryType === 'delivery' ? 'DIANTAR KURIR' : 'AMBIL DI CABANG');
                        if (rMethod) rMethod.innerText = window.currentPaymentMethod.toUpperCase();
                        if (rFee) rFee.innerText = (fee > 0 ? 'Rp ' + window.formatRupiah(fee) : 'Rp 0');
                        if (rTotal) rTotal.innerText = 'Rp ' + window.formatRupiah(grandTotal);

                        if (btnPrint) {
                            btnPrint.href = window.RECEIPT_URL_PREFIX + "/" + trx.id + "/receipt";
                        }

                        $('#modal-success-receipt').modal('show');
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: response.status || 'warning',
                                title: response.message || 'Gagal memproses transaksi.'
                            });
                        } else {
                            alert(response.message || 'Gagal memproses transaksi.');
                        }
                    }
                },
                error: function(xhr) {
                    if (btn) btn.disabled = false;
                    if (btnText) btnText.innerText = (window.currentPaymentMethod === 'cash' ? 'Buat Pesanan & Bayar Cash' : 'Lanjut ke Pembayaran Xendit');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Kesalahan', text: msg });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: "Konfirmasi Pesanan",
                html: '<div class="text-start p-3 bg-light rounded-3 text-xs space-y-2">' +
                      '<div class="d-flex justify-content-between"><span>Paket:</span><b>' + window.PRODUCT_NAME + '</b></div>' +
                      '<div class="d-flex justify-content-between"><span>Penyerahan:</span><b>' + deliveryLabel + '</b></div>' +
                      '<div class="d-flex justify-content-between"><span>Metode Bayar:</span><b>' + paymentLabel + '</b></div>' +
                      '<hr class="my-1">' +
                      '<div class="d-flex justify-content-between fs-6 fw-bold text-primary"><span>Total:</span><span>Rp ' + window.formatRupiah(grandTotal) + '</span></div>' +
                      '</div>',
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#2563EB",
                cancelButtonColor: "#64748B",
                confirmButtonText: "Ya, Proses Sekarang",
                cancelButtonText: "Batal",
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'rounded-2 font-semibold px-4 py-2',
                    cancelButton: 'rounded-2 font-medium px-4 py-2'
                }
            }).then(function(result) {
                if (result.isConfirmed) {
                    executeOrder();
                }
            });
        } else {
            if (confirm("Konfirmasi Pesanan: Total Rp " + window.formatRupiah(grandTotal) + " (" + deliveryLabel + ")?")) {
                executeOrder();
            }
        }
    };
</script>
@endpush
