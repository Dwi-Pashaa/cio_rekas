@extends('layouts.app')

@section('title', 'Data Agent')
@section('pretitle', 'Master Data')

@section('header-actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" id="addBtn" class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 rounded-2" data-bs-toggle="modal" data-bs-target="#modal-customer">
            <x-icons.plus class="w-4 h-4" />
            <span class="fw-semibold">Tambah Agent</span>
        </button>

        @can('download excel')
            <a href="{{ route('customer.export') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-2 px-3 py-2 rounded-2">
                <x-icons.download class="w-4 h-4" />
                <span class="fw-semibold">Export Excel</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="card shadow-sm border-0 rounded-3 mb-4">
    
    {{-- Header & Search Filter Bar --}}
    <div class="card-body border-bottom py-3 px-4">
        <div class="row g-3 align-items-center justify-content-between">
            {{-- Filter Sort Dropdown --}}
            <div class="col-12 col-md-auto d-flex align-items-center gap-2.5">
                <label for="sort" class="text-slate-600 fw-semibold fs-6 m-0">Tampilkan:</label>
                <select name="sort" id="sort" class="form-select form-select-md w-auto rounded-xl fw-semibold border-slate-300 py-2 px-3 shadow-none">
                    @foreach ([10, 25, 50, 100] as $opt)
                        <option value="{{ $opt }}" {{ request('sort') == $opt ? 'selected' : '' }}>{{ $opt }} baris</option>
                    @endforeach
                </select>
            </div>

            {{-- Search Bar --}}
            <div class="col-12 col-md-5 ms-auto">
                <form method="GET" action="{{ route('customer.index') }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-slate-400 ps-3 rounded-start-xl">
                            <x-icons.search class="w-5 h-5 text-slate-400" />
                        </span>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            class="form-control form-control-md border-start-0 border-slate-300 ps-1 py-2 text-slate-800" 
                            placeholder="Cari nama, username, serial number, no telp, email..."
                            style="font-size: 0.925rem;"
                        />
                        @if(request('search'))
                            <a href="{{ route('customer.index') }}" class="btn btn-outline-secondary d-flex align-items-center px-3">
                                <x-icons.close class="w-4 h-4" />
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-4 fw-bold rounded-end-xl">
                            Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Responsive Table Container --}}
    <div class="table-responsive">
        <table class="table table-hover table-vcenter card-table align-middle text-nowrap mb-0">
            <thead class="bg-light text-muted text-uppercase fs-7 fw-bold border-bottom">
                <tr>
                    <th class="w-1 text-center py-3.5">No</th>
                    <th class="py-3.5">Agent & Akun Login</th>
                    <th class="py-3.5">Serial Number</th>
                    <th class="py-3.5">Barang / Paket</th>
                    <th class="py-3.5 text-center">Limit</th>
                    <th class="py-3.5 text-center">Tipe Bayar</th>
                    <th class="py-3.5 text-center">Status</th>
                    <th class="py-3.5 text-center" style="min-width: 250px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($customers as $item)
                    <tr>
                        {{-- No --}}
                        <td class="text-center text-muted fw-semibold">
                            {{ $loop->iteration + ($customers->firstItem() ? $customers->firstItem() - 1 : 0) }}
                        </td>

                        {{-- Agent (Avatar + Nama + NIK + Username + Telp + Email + Alamat) --}}
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="w-10 h-10 rounded-circle bg-blue-50 text-blue-700 border border-blue-200 fw-bold d-flex align-items-center justify-center me-3 shrink-0 fs-6">
                                    {{ strtoupper(substr($item->name, 0, 2)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark fs-6">{{ $item->name }}</span>
                                        @if($item->user)
                                            <span class="badge bg-blue-subtle text-blue-800 border border-blue-200 px-1.5 py-0.5 font-monospace" style="font-size: 0.68rem;" title="Username Login Agent">
                                                <x-icons.users class="w-3 h-3 inline me-0.5" />{{ $item->user->username }}
                                            </span>
                                        @endif
                                        @if($item->nik)
                                            <span class="badge bg-light text-slate-600 border px-1.5 py-0.5 font-monospace" style="font-size: 0.68rem;" title="NIK: {{ $item->nik }}">
                                                NIK: {{ $item->nik }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center flex-wrap gap-2 text-muted small mt-0.5">
                                        @if($item->email)
                                            <span class="text-primary-emphasis fw-medium">{{ $item->email }}</span>
                                        @endif
                                        @if($item->email && $item->telp)
                                            <span>&bull;</span>
                                        @endif
                                        @if($item->telp)
                                            <span>{{ $item->telp }}</span>
                                        @endif
                                        @if(($item->email || $item->telp) && $item->address)
                                            <span>&bull;</span>
                                        @endif
                                        @if($item->address)
                                            <span class="text-truncate" style="max-width: 220px;" title="{{ $item->address }}">
                                                {{ $item->address }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Serial Number Badge --}}
                        <td>
                            <div class="d-inline-flex flex-column">
                                <span class="badge bg-light text-primary border px-2.5 py-1.5 rounded-2 font-monospace fw-bold fs-7">
                                    {{ $item->code }}
                                </span>
                            </div>
                        </td>

                        {{-- Barang / Paket & Cabang Asal --}}
                        <td>
                            @if($item->product)
                                <div class="fw-semibold text-dark">{{ $item->product->name }}</div>
                                <div class="d-flex align-items-center flex-wrap gap-1.5 mt-1">
                                    <span class="text-muted small font-monospace">Kode: {{ $item->product->code }}</span>
                                    <span class="badge bg-purple-subtle text-purple-700 border border-purple-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.68rem;" title="Cabang asal barang agent">
                                        <x-icons.branch class="w-3 h-3 inline me-0.5" />
                                        Cabang: {{ optional(optional($item->product)->branch)->name ?? 'Semua Cabang' }}
                                    </span>
                                </div>
                            @else
                                <span class="text-muted fst-italic small">Belum ada paket</span>
                            @endif
                        </td>

                        {{-- Limit Kuota --}}
                        <td class="text-center">
                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-2 fw-bold">
                                {{ $item->limit }} Unit
                            </span>
                        </td>

                        {{-- Tipe Pembayaran Diizinkan (Cash / Transfer) --}}
                        <td class="text-center">
                            @php
                                $methods = (array) ($item->payment_methods ?? ['cash', 'transfer']);
                            @endphp
                            <div class="d-flex align-items-center justify-content-center flex-wrap gap-1">
                                @if(in_array('cash', $methods))
                                    <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.68rem;">
                                        <x-icons.cash class="w-3 h-3 inline me-0.5" />Cash
                                    </span>
                                @endif
                                @if(in_array('transfer', $methods))
                                    <span class="badge bg-indigo-50 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.68rem;">
                                        <x-icons.receipt class="w-3 h-3 inline me-0.5" />Transfer
                                    </span>
                                @endif
                                @if(empty($methods))
                                    <span class="text-muted small fst-italic">Semua</span>
                                @endif
                            </div>
                        </td>

                        {{-- Status Pelanggan --}}
                        <td class="text-center">
                            @php
                                $statusName = strtolower(optional($item->status)->name ?? '');
                                $badgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                if (str_contains($statusName, 'aktif') || str_contains($statusName, 'lunas')) {
                                    $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                                } elseif (str_contains($statusName, 'blokir') || str_contains($statusName, 'nonaktif')) {
                                    $badgeClass = 'bg-danger-subtle text-danger border-danger-subtle';
                                } elseif (str_contains($statusName, 'pending') || str_contains($statusName, 'tunda')) {
                                    $badgeClass = 'bg-warning-subtle text-warning border-warning-subtle';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }} border px-2.5 py-1 rounded-pill fw-semibold">
                                {{ optional($item->status)->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1.5">
                                {{-- Tombol Salin Link NFC --}}
                                <button 
                                    type="button" 
                                    onclick="copyNfcLink('{{ $item->nfc_url }}')" 
                                    class="btn btn-sm btn-outline-info px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Salin Link Transaksi NFC untuk NFC Tools"
                                >
                                    <x-icons.categories class="w-4 h-4" />
                                    <span>NFC</span>
                                </button>

                                {{-- Tombol Member Card --}}
                                <button 
                                    type="button" 
                                    onclick="memberCard('{{ $item->id }}')" 
                                    class="btn btn-sm btn-outline-primary px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Lihat & Cetak Kartu Member"
                                >
                                    <x-icons.barcode class="w-4 h-4" />
                                    <span>Member</span>
                                </button>

                                {{-- Tombol Edit --}}
                                <button 
                                    type="button" 
                                    onclick="editModal('{{ $item->id }}')" 
                                    class="btn btn-sm btn-outline-warning px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Edit Agent"
                                >
                                    <x-icons.edit class="w-4 h-4" />
                                    <span>Edit</span>
                                </button>

                                {{-- Tombol Hapus --}}
                                <button 
                                    type="button" 
                                    onclick="deleteItem('{{ $item->id }}')" 
                                    class="btn btn-sm btn-outline-danger px-2.5 py-1.5 rounded-2 fw-semibold d-inline-flex align-items-center gap-1 shadow-none" 
                                    title="Hapus Agent"
                                >
                                    <x-icons.trash class="w-4 h-4" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <x-icons.users class="w-12 h-12 mx-auto mb-2 text-muted opacity-50" />
                            <div class="fw-semibold">Tidak ada data agent ditemukan</div>
                            @if(request('search'))
                                <div class="small text-muted mt-1">Coba kata kunci pencarian yang lain.</div>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer Pagination --}}
    <div class="card-footer d-flex flex-column flex-sm-row align-items-center justify-content-between py-3 gap-2 bg-white">
        <p class="m-0 text-muted small">
            Menampilkan <b>{{ $customers->firstItem() ?? 0 }}</b> sampai <b>{{ $customers->lastItem() ?? 0 }}</b> dari <b>{{ $customers->total() }}</b> entri agent
        </p>
        <div class="m-0">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@push('modal')
    {{-- Modal Tambah / Edit Agent --}}
    <div class="modal fade" id="modal-customer" tabindex="-1" aria-labelledby="modalCustomerTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-3 border-0 shadow">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark" id="modalCustomerTitle">Tambah Agent Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="type" id="type" value="create">
                    <input type="hidden" name="id" id="id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="code" class="form-label fw-bold text-muted small text-uppercase">Serial Number (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control form-control-md rounded-2" placeholder="Contoh: SN-100234">
                            <span class="invalid-feedback error_code"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="name" class="form-label fw-bold text-muted small text-uppercase">Nama Lengkap Agent <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control form-control-md rounded-2" placeholder="Masukkan nama agent">
                            <span class="invalid-feedback error_name"></span>
                        </div>

                        {{-- Input Username & Password Login Akun Agent --}}
                        <div class="col-md-6">
                            <label for="username" class="form-label fw-bold text-muted small text-uppercase">Username Login <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0">@</span>
                                <input type="text" name="username" id="username" class="form-control form-control-md border-start-0 rounded-end-2" placeholder="Contoh: agent_budi">
                            </div>
                            <span class="invalid-feedback error_username d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-bold text-muted small text-uppercase">
                                Password Login <span class="text-danger" id="password_req_star">*</span>
                            </label>
                            <input type="password" name="password" id="password" class="form-control form-control-md rounded-2" placeholder="Minimal 6 karakter">
                            <div class="small text-muted d-none mt-1" id="password_help_text" style="font-size: 0.72rem;">Kosongkan jika tidak ingin mengubah password akun.</div>
                            <span class="invalid-feedback error_password d-block"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="telp" class="form-label fw-bold text-muted small text-uppercase">No. Telepon / WA <span class="text-danger">*</span></label>
                            <input type="text" name="telp" id="telp" class="form-control form-control-md rounded-2" placeholder="08xxxxxxxxxx">
                            <span class="invalid-feedback error_telp"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label fw-bold text-muted small text-uppercase">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control form-control-md rounded-2" placeholder="agent@email.com">
                            <span class="invalid-feedback error_email"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="nik" class="form-label fw-bold text-muted small text-uppercase">NIK / No. KTP <span class="text-muted fw-normal text-capitalize" style="font-size: 0.75rem;">(Opsional)</span></label>
                            <input type="text" name="nik" id="nik" class="form-control form-control-md rounded-2" placeholder="Masukkan 16 digit NIK">
                            <span class="invalid-feedback error_nik"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="products_id" class="form-label fw-bold text-muted small text-uppercase">Paket Barang & Cabang <span class="text-danger">*</span></label>
                            <select name="products_id" id="products_id" class="form-select form-select-md rounded-2">
                                <option value="">Pilih Barang / Paket</option>
                                @foreach ($products as $pd)
                                    <option value="{{ $pd->id }}">
                                        {{ $pd->name }} [Cabang: {{ optional($pd->branch)->name ?? 'Semua Cabang' }}] ({{ $pd->code }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback error_products_id"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="limit" class="form-label fw-bold text-muted small text-uppercase">Limit Kuota Transaksi <span class="text-danger">*</span></label>
                            <input type="number" name="limit" id="limit" class="form-control form-control-md rounded-2" placeholder="Contoh: 1" value="1" min="1">
                            <span class="invalid-feedback error_limit"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="types_id" class="form-label fw-bold text-muted small text-uppercase">Tipe Agent <span class="text-danger">*</span></label>
                            <select name="types_id" id="types_id" class="form-select form-select-md rounded-2">
                                <option value="">Pilih Tipe</option>
                                @foreach ($customerTypes as $tp)
                                    <option value="{{ $tp->id }}">{{ $tp->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback error_types_id"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="status_id" class="form-label fw-bold text-muted small text-uppercase">Status Agent <span class="text-danger">*</span></label>
                            <select name="status_id" id="status_id" class="form-select form-select-md rounded-2">
                                <option value="">Pilih Status</option>
                                @foreach ($customerStatus as $st)
                                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback error_status_id"></span>
                        </div>

                        <div class="col-md-6">
                            <label for="address" class="form-label fw-bold text-muted small text-uppercase">Alamat <span class="text-danger">*</span></label>
                            <input type="text" name="address" id="address" class="form-control form-control-md rounded-2" placeholder="Alamat lengkap agent">
                            <span class="invalid-feedback error_address"></span>
                        </div>

                        {{-- 2 Checkbox Tipe Pembayaran (Cash & Transfer) --}}
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small text-uppercase d-block mb-1.5">
                                Tipe Pembayaran Diizinkan <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex align-items-center gap-4 p-3 bg-light rounded-3 border">
                                <label class="form-check form-check-inline m-0 d-flex align-items-center gap-2 cursor-pointer">
                                    <input class="form-check-input payment-method-check" type="checkbox" name="payment_methods[]" value="cash" id="pay_cash" checked>
                                    <span class="form-check-label fw-bold text-dark fs-7 d-flex align-items-center gap-1.5">
                                        <x-icons.cash class="w-4 h-4 text-emerald-600" />
                                        Cash (Tunai)
                                    </span>
                                </label>
                                <label class="form-check form-check-inline m-0 d-flex align-items-center gap-2 cursor-pointer">
                                    <input class="form-check-input payment-method-check" type="checkbox" name="payment_methods[]" value="transfer" id="pay_transfer" checked>
                                    <span class="form-check-label fw-bold text-dark fs-7 d-flex align-items-center gap-1.5">
                                        <x-icons.receipt class="w-4 h-4 text-indigo-600" />
                                        Transfer (Non-Tunai / Bank)
                                    </span>
                                </label>
                            </div>
                            <span class="invalid-feedback error_payment_methods d-block mt-1"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-2" data-bs-dismiss="modal">Batal</button>
                    <button type="button" id="storeBtn" class="btn btn-primary px-4 rounded-2 fw-semibold">Simpan Data Agent</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Kartu Member --}}
    <div class="modal fade" id="modal-member" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom py-2.5 px-4 bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <x-icons.barcode class="w-5 h-5 text-primary" />
                        <h6 class="modal-title fw-bold text-dark mb-0">Kartu Member Agent Resmi</h6>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4 bg-light text-center" id="wrapper-container">
                    {{-- Kartu Desain Mewah & Proporsional (Standard Card Ratio 1.58:1) --}}
                    <div id="wrapper" class="rounded-4 p-4 text-white shadow position-relative text-start overflow-hidden mx-auto" 
                         style="background: linear-gradient(135deg, #091E3A 0%, #173B6C 40%, #1E40AF 75%, #2563EB 100%); width: 100%; max-width: 500px; min-height: 290px; border: 1px solid rgba(255, 255, 255, 0.25); display: flex; flex-direction: column; justify-content: space-between;">
                        
                        {{-- Background Watermark Accent --}}
                        <div class="position-absolute" style="top: -40px; right: -40px; width: 180px; height: 180px; border-radius: 50%; border: 25px solid rgba(255, 255, 255, 0.04); pointer-events: none;"></div>
                        <div class="position-absolute" style="bottom: -60px; left: -30px; width: 220px; height: 220px; border-radius: 50%; border: 30px solid rgba(255, 255, 255, 0.03); pointer-events: none;"></div>

                        {{-- Baris Atas: Logo & Badge Status VIP --}}
                        <div class="d-flex align-items-center justify-content-between position-relative z-1">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="w-9 h-9 rounded-2 bg-white text-blue-800 d-flex align-items-center justify-center font-bold shadow-sm">
                                    <x-icons.cart class="w-5 h-5 text-blue-700" />
                                </div>
                                <div>
                                    <div class="fw-bold tracking-tight text-white fs-6 lh-sm">CIO REKAS</div>
                                    <div class="text-uppercase fw-semibold" style="font-size: 0.62rem; letter-spacing: 0.14em; color: #93C5FD;">
                                        VIP AGENT CARD
                                    </div>
                                </div>
                            </div>
                            
                            <span class="badge rounded-pill fw-bold shadow-sm" id="sts"
                                  style="background: linear-gradient(90deg, #F59E0B, #FBBF24); color: #0F172A; font-size: 0.72rem; letter-spacing: 0.08em; padding: 6px 14px;">
                                MEMBER
                            </span>
                        </div>

                        {{-- Baris Tengah: EMV Chip + Serial Number + Nama Agent --}}
                        <div class="my-2 position-relative z-1">
                            {{-- EMV Gold Chip Icon --}}
                            <div class="d-inline-flex align-items-center justify-content-center p-1 rounded-2 mb-2" 
                                 style="background: linear-gradient(135deg, #D4AF37 0%, #FFF8DC 50%, #AA771C 100%); width: 44px; height: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.3); border: 1px solid rgba(0,0,0,0.15);">
                                <div style="width: 100%; height: 100%; border: 1px solid rgba(0,0,0,0.2); border-radius: 3px; position: relative;">
                                    <div style="position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(0,0,0,0.25);"></div>
                                    <div style="position: absolute; top: 0; bottom: 0; left: 50%; width: 1px; background: rgba(0,0,0,0.25);"></div>
                                </div>
                            </div>

                            {{-- Serial Number (Clean & Distinct) --}}
                            <div class="font-monospace fw-bold text-white fs-5 tracking-wider mb-1" id="serial" style="letter-spacing: 0.16em; text-shadow: 0 2px 4px rgba(0,0,0,0.4);">
                                0000 0000 00
                            </div>

                            {{-- Nama Agent (Uppercase & Prominent) --}}
                            <div class="fw-extrabold text-white fs-4 text-uppercase tracking-wide text-truncate" id="name_customer" style="text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                                NAMA AGENT
                            </div>
                        </div>

                        {{-- Baris Bawah: Detail Paket & Barcode Compact di Sudut Kanan --}}
                        <div class="d-flex align-items-end justify-content-between pt-2 position-relative z-1" style="border-top: 1px solid rgba(255, 255, 255, 0.15);">
                            {{-- Metadata Paket & Limit --}}
                            <div class="d-flex align-items-center gap-4">
                                <div>
                                    <div class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.62rem; letter-spacing: 0.08em;">PAKET BARANG</div>
                                    <div class="fw-bold text-white small text-truncate" style="max-width: 180px;" id="brg">-</div>
                                </div>
                                <div>
                                    <div class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.62rem; letter-spacing: 0.08em;">KUOTA LIMIT</div>
                                    <div class="fw-bold text-white small" id="lmt">-</div>
                                </div>
                            </div>

                            {{-- Barcode Box (Compact & Sharp) --}}
                            <div class="bg-white rounded-2 px-2 py-1 shadow-sm d-flex flex-column align-items-center justify-content-center">
                                <svg id="barcodeMember" style="height: 36px; max-width: 140px;"></svg>
                            </div>
                        </div>
                    </div>

                    {{-- Box Salin Link NFC --}}
                    <div class="mt-3 text-start p-2.5 bg-white rounded-3 border border-slate-200">
                        <label class="text-xs fw-bold text-slate-600 text-uppercase d-block mb-1">
                            Link Transaksi NFC (Untuk ditulis ke NFC Tag):
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="modal_nfc_url" readonly class="form-control form-control-sm bg-light text-slate-700 font-monospace text-xs" value="" />
                            <button type="button" onclick="copyNfcFromModal()" class="btn btn-info px-3 fw-bold d-inline-flex align-items-center gap-1">
                                <x-icons.categories class="w-3.5 h-3.5" />
                                <span>Salin Link</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 bg-white d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary px-3 rounded-2" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" onclick="printMemberCard()" class="btn btn-outline-primary px-3 rounded-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                            <x-icons.printer class="w-4 h-4" />
                            <span>Cetak Struk/Kartu</span>
                        </button>
                        <button type="button" onclick="downloadWrapperImage()" class="btn btn-primary px-3 rounded-2 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-sm">
                            <x-icons.download class="w-4 h-4" />
                            <span>Download PNG</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    const BASE = "{{ route('customer.index') }}";

    // Sorting selector
    let params = new URLSearchParams(window.location.search);
    $("#sort").change(function() {
        params.set('sort', $(this).val());
        window.location.href = BASE + '?' + params.toString();
    });

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
        $("#modalCustomerTitle").html("Tambah Agent Baru");
        $("#code").val("");
        $("#name").val("");
        $("#username").val("");
        $("#password").val("");
        $("#password_req_star").removeClass("d-none");
        $("#password_help_text").addClass("d-none");
        $("#telp").val("");
        $("#email").val("");
        $("#nik").val("");
        $("#address").val("");
        $("#products_id").val("");
        $("#limit").val("1");
        $("#types_id").val("");
        $("#status_id").val("");
        $("#pay_cash").prop("checked", true);
        $("#pay_transfer").prop("checked", true);
        $("#type").val("create");
        $("#id").val("");
        $(".form-control, .form-select").removeClass("is-invalid");
        $(".invalid-feedback").html("");
    });

    $("#storeBtn").click(function() {
        let id = $("#id").val();
        let type = $("#type").val();
        let code = $("#code").val();
        let name = $("#name").val();
        let username = $("#username").val();
        let password = $("#password").val();
        let telp = $("#telp").val();
        let email = $("#email").val();
        let nik = $("#nik").val();
        let address = $("#address").val();
        let products_id = $("#products_id").val();
        let limit = $("#limit").val();
        let types_id = $("#types_id").val();
        let status_id = $("#status_id").val();

        // Ambil pilihan checkbox tipe pembayaran
        let payment_methods = [];
        $(".payment-method-check:checked").each(function() {
            payment_methods.push($(this).val());
        });

        let url = (type === 'create') ? BASE + '/store' : BASE + `/${id}/update`;
        let method = (type === 'create') ? "POST" : "PUT";
        
        $.ajax({
            url: url,
            method: method,
            data: {
                code: code,
                name: name,
                username: username,
                password: password,
                telp: telp,
                email: email,
                nik: nik,
                address: address,
                products_id: products_id,
                limit: limit,
                types_id: types_id,
                status_id: status_id,
                payment_methods: payment_methods
            },
        }).done(function(response) {
            if (response.errors) {
                $.each(response.errors, function(index, value) {
                    let cleanIndex = index.replace('.', '_');
                    $("#" + cleanIndex).addClass('is-invalid');
                    $(".error_" + cleanIndex).html(value);

                    setTimeout(() => {
                        $("#" + cleanIndex).removeClass('is-invalid');
                        $(".error_" + cleanIndex).html('');
                    }, 4000);
                });                
            } else if (response.code === 200) {
                $("#modal-customer").modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Data agent berhasil disimpan.'
                });

                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Toast.fire({
                    icon: response.status || 'warning',
                    title: response.message || 'Gagal memproses data.'
                });
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
            Toast.fire({ icon: 'error', title: 'Terjadi kesalahan sistem.' });
        });
    });

    function editModal(id) {
        $.ajax({
            url: BASE + `/${id}/show`,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            $("#modalCustomerTitle").html("Edit Data Agent");
            let data = response.data;
            $("#id").val(data.id);
            $("#code").val(data.code);
            $("#name").val(data.name);
            $("#username").val(data.user_username || (data.user ? data.user.username : ''));
            $("#password").val("");
            $("#password_req_star").addClass("d-none");
            $("#password_help_text").removeClass("d-none");
            $("#telp").val(data.telp);
            $("#email").val(data.email || "");
            $("#nik").val(data.nik || "");
            $("#address").val(data.address);
            $("#products_id").val(data.products_id);
            $("#limit").val(data.limit);
            $("#types_id").val(data.types_id);
            $("#status_id").val(data.status_id);

            // Centang checkbox tipe pembayaran
            let methods = data.payment_methods || ['cash', 'transfer'];
            $("#pay_cash").prop("checked", methods.includes('cash'));
            $("#pay_transfer").prop("checked", methods.includes('transfer'));

            $("#type").val("update");
            $(".form-control, .form-select").removeClass("is-invalid");
            $(".invalid-feedback").html("");
            $("#modal-customer").modal('show');
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function deleteItem(id) {
        Swal.fire({
            title: "Konfirmasi Hapus",
            text: "Data agent dan akun loginnya akan dihapus secara permanen dari sistem.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#EF4444",
            cancelButtonColor: "#64748B",
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Batal",
            customClass: {
                popup: 'rounded-3',
                confirmButton: 'rounded-2 font-semibold px-4 py-2',
                cancelButton: 'rounded-2 font-medium px-4 py-2'
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
                            title: response.message || 'Agent berhasil dihapus.'
                        });

                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        Toast.fire({
                            icon: "error",
                            title: "Gagal menghapus data agent."
                        });
                    }
                });
            }
        });
    }

    function memberCard(id) {
        $.ajax({
            url: BASE + `/${id}/show`,
            method: "GET",
            dataType: "json"
        }).done(function(response){
            let data = response.data;
            
            $("#modal-member").modal('show');

            $("#serial").html(data.code);
            let branchLabel = (data.product && data.product.branch) ? ' (' + data.product.branch.name + ')' : '';
            $("#brg").html(data.product ? data.product.name + branchLabel : '-');
            $("#lmt").html((data.limit || '0') + ' Unit');
            $("#sts").html(data.status ? data.status.name.toUpperCase() : 'VIP MEMBER');
            $("#modal_nfc_url").val(data.nfc_url || '');

            JsBarcode("#barcodeMember", data.code, {
                format: "CODE128",
                displayValue: false,
                height: 32,
                width: 1.3,
                margin: 0
            });
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.log("Error:", textStatus, errorThrown);
        });
    }

    function copyNfcLink(url) {
        if (!url) {
            Toast.fire({ icon: 'warning', title: 'Link NFC belum tersedia.' });
            return;
        }
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(() => {
                Toast.fire({ icon: 'success', title: 'Link Transaksi NFC berhasil disalin ke clipboard!' });
            }).catch(() => {
                fallbackCopyText(url);
            });
        } else {
            fallbackCopyText(url);
        }
    }

    function fallbackCopyText(text) {
        let temp = document.createElement("textarea");
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand("copy");
        document.body.removeChild(temp);
        Toast.fire({ icon: 'success', title: 'Link Transaksi NFC berhasil disalin ke clipboard!' });
    }

    function copyNfcFromModal() {
        let url = $("#modal_nfc_url").val();
        copyNfcLink(url);
    }

    function printMemberCard() {
        const element = document.getElementById('wrapper');
        html2canvas(element, {
            scale: 3, 
            useCORS: true,
            backgroundColor: null
        }).then(canvas => {
            let imgData = canvas.toDataURL('image/png');
            let printWindow = window.open('', '_blank');
            printWindow.document.write('<!DOCTYPE html><html><head><title>Cetak Kartu Member</title>');
            printWindow.document.write('<style>@page { size: auto; margin: 15mm; } body { margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; background: #fff; font-family: sans-serif; } img { max-width: 480px; width: 100%; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write('<img src="' + imgData + '" />');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        });
    }

    function downloadWrapperImage() {
        const element = document.getElementById('wrapper');
        html2canvas(element, {
            scale: 3, 
            useCORS: true,
            backgroundColor: null
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'kartu-member-' + ($("#serial").text().trim() || 'pelanggan') + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }
</script>
@endpush