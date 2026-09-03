<aside class="navbar navbar-vertical navbar-expand-lg">
    <div class="container-fluid">
        {{-- Mobile Hamburger Toggle --}}
        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        {{-- Brand Logo Header --}}
        <div class="navbar-brand py-3 px-3">
            <a href="{{ route('dashboard') }}" class="d-flex align-items-center gap-2.5 text-decoration-none">
                <div class="p-1 rounded-2 bg-white d-flex align-items-center justify-center shadow-sm shrink-0" style="width: 40px; height: 40px;">
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name', env('APP_NAME', 'CIO REKAS')) }}" class="img-fluid" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                </div>
                <div class="d-flex flex-column text-start overflow-hidden">
                    <span class="font-bold text-white tracking-tight text-truncate" style="font-size: 1.12rem; line-height: 1.2;">
                        {{ config('app.name', env('APP_NAME', 'CIO REKAS')) }}
                    </span>
                    <span class="text-uppercase fw-semibold" style="color: #93C5FD; font-size: 0.65rem; letter-spacing: 0.12em;">
                        Point of Sale
                    </span>
                </div>
            </a>
        </div>

        {{-- Mobile User Avatar Dropdown --}}
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
                    <div class="w-8 h-8 rounded-circle bg-white text-blue-800 font-bold d-flex align-items-center justify-center text-xs shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-xl p-2 mt-1">
                    <div class="px-3 py-2 border-bottom border-slate-700 mb-1">
                        <div class="fw-bold text-white">{{ Auth::user()->name }}</div>
                        <div class="small text-slate-400">{{ Auth::user()->email }}</div>
                    </div>
                    <a href="{{ route('logout') }}" class="dropdown-item text-rose-400 rounded-lg">
                        <x-icons.logout class="w-4 h-4 me-2" />
                        Keluar
                    </a>
                </div>
            </div>
        </div>

        {{-- Collapsible Sidebar Menu --}}
        <div class="collapse navbar-collapse" id="sidebar-menu">
            {{-- User Session Info Box (Desktop) --}}
            <div class="d-none d-lg-flex align-items-center gap-3 p-2.5 my-2 mx-2 rounded-3 shadow-xs" 
                 style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.16);">
                <div class="rounded-circle bg-white text-blue-800 fw-extrabold d-flex align-items-center justify-center text-xs shadow-xs shrink-0" 
                     style="width: 36px; height: 36px; min-width: 36px;">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                </div>
                <div class="overflow-hidden flex-fill">
                    <div class="text-white fw-bold text-truncate lh-sm" style="font-size: 0.875rem;">
                        {{ Auth::user()->name }}
                    </div>
                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                        <span class="badge px-2 py-0.5 rounded-pill" style="background: rgba(147, 197, 253, 0.25); color: #DBEAFE; font-size: 0.68rem; letter-spacing: 0.04em;">
                            {{ Auth::user()->getRoleNames()[0] ?? 'Kasir' }}
                        </span>
                        @if(Auth::user()->branch)
                            <span class="text-white-50 small">&bull;</span>
                            <span class="text-truncate small" style="color: #93C5FD; font-size: 0.7rem;" title="{{ Auth::user()->branch->name }}">
                                {{ Auth::user()->branch->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <ul class="navbar-nav pt-lg-2">
                {{-- KELOMPOK 1: MENU UTAMA --}}
                <li class="nav-item pt-2 pb-1 px-3">
                    <span class="text-uppercase fw-bold text-blue-200" style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.75;">
                        Menu Utama
                    </span>
                </li>

                {{-- 1. Dashboard --}}
                <li class="nav-item {{ Route::is('dashboard*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block">
                            <x-icons.dashboard class="w-5 h-5" />
                        </span>
                        <span class="nav-link-title">Dashboard</span>
                    </a>
                </li>

                {{-- 2. Kasir POS --}}
                @can('tambah transaksi')
                    @php
                        $isKasirPosActive = Route::is('transaksi.create') || Route::is('transaksi.agent.create');
                    @endphp
                    <li class="nav-item my-1 {{ $isKasirPosActive ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('transaksi.create') }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <x-icons.cart class="w-5 h-5" />
                            </span>
                            <span class="nav-link-title font-bold">Kasir POS</span>
                        </a>
                    </li>
                @endcan

                {{-- KELOMPOK 2: TRANSAKSI & KEUANGAN --}}
                @if (auth()->user()->can('lihat transaksi') || auth()->user()->can('lihat grafik transaksi') || auth()->user()->can('lihat keuangan'))
                    <li class="nav-item pt-3 pb-1 px-3">
                        <span class="text-uppercase fw-bold text-blue-200" style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.75;">
                            Transaksi & Keuangan
                        </span>
                    </li>

                    {{-- 3. Transaksi (Dropdown) --}}
                    @if (auth()->user()->can('lihat transaksi') || auth()->user()->can('lihat grafik transaksi'))
                        @php
                            $isTransaksiActive = Route::is('transaksi.*') && !Route::is('transaksi.create') && !Route::is('transaksi.agent.create');
                        @endphp
                        <li class="nav-item dropdown {{ $isTransaksiActive ? 'active show' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-transaksi" data-bs-toggle="dropdown"
                                data-bs-auto-close="false" role="button" aria-expanded="{{ $isTransaksiActive ? 'true' : 'false' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <x-icons.receipt class="w-5 h-5" />
                                </span>
                                <span class="nav-link-title">Transaksi</span>
                            </a>
                            <div class="dropdown-menu {{ $isTransaksiActive ? 'show' : '' }}">
                                @can('lihat transaksi')
                                    <a class="dropdown-item {{ Route::is('transaksi.index') ? 'active' : '' }}" href="{{ route('transaksi.index') }}">
                                        <x-icons.clock class="w-4 h-4" />
                                        <span>Riwayat Transaksi</span>
                                    </a>
                                @endcan
                                @can('lihat grafik transaksi')
                                    <a class="dropdown-item {{ Route::is('transaksi.chart') ? 'active' : '' }}" href="{{ route('transaksi.chart') }}">
                                        <x-icons.trending-up class="w-4 h-4" />
                                        <span>Grafik Penjualan</span>
                                    </a>
                                @endcan
                            </div>
                        </li>
                    @endif

                    {{-- 4. Laporan Keuangan --}}
                    @if (auth()->user()->can('lihat keuangan'))
                        <li class="nav-item {{ Route::is('keuangan*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('keuangan.index') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <x-icons.finance class="w-5 h-5" />
                                </span>
                                <span class="nav-link-title">Laporan Keuangan</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- KELOMPOK 3: INVENTORI & DISTRIBUSI --}}
                @if (auth()->user()->can('lihat barang') || auth()->user()->can('lihat kategori') || auth()->user()->can('lihat cabang') || auth()->user()->can('distribusi utama') || auth()->user()->can('distribusi cabang') || auth()->user()->can('lihat riwayat distribusi') || auth()->user()->hasRole('Admin'))
                    <li class="nav-item pt-3 pb-1 px-3">
                        <span class="text-uppercase fw-bold text-blue-200" style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.75;">
                            Inventori & Distribusi
                        </span>
                    </li>

                    {{-- 5. Barang & Cabang (Dropdown) --}}
                    @if (auth()->user()->can('lihat barang') || auth()->user()->can('lihat kategori') || auth()->user()->can('lihat cabang'))
                        @php
                            $isBarangActive = Route::is('produk.*') || Route::is('kategori.*') || Route::is('branch.*');
                        @endphp
                        <li class="nav-item dropdown {{ $isBarangActive ? 'active show' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-barang" data-bs-toggle="dropdown"
                                data-bs-auto-close="false" role="button" aria-expanded="{{ $isBarangActive ? 'true' : 'false' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <x-icons.products class="w-5 h-5" />
                                </span>
                                <span class="nav-link-title">Barang & Cabang</span>
                            </a>
                            <div class="dropdown-menu {{ $isBarangActive ? 'show' : '' }}">
                                @can('lihat barang')
                                    <a class="dropdown-item {{ Route::is('produk.*') ? 'active' : '' }}" href="{{ route('produk.index') }}">
                                        <x-icons.package class="w-4 h-4" />
                                        <span>Stok Barang</span>
                                    </a>
                                @endcan
                                @can('lihat kategori')
                                    <a class="dropdown-item {{ Route::is('kategori.*') ? 'active' : '' }}" href="{{ route('kategori.index') }}">
                                        <x-icons.categories class="w-4 h-4" />
                                        <span>Kategori Barang</span>
                                    </a>
                                @endcan
                                @can('lihat cabang')
                                    <a class="dropdown-item {{ Route::is('branch.*') ? 'active' : '' }}" href="{{ route('branch.index') }}">
                                        <x-icons.branch class="w-4 h-4" />
                                        <span>Data Cabang</span>
                                    </a>
                                @endcan
                            </div>
                        </li>
                    @endif

                    {{-- 6. Distribusi Voucher (Dropdown) --}}
                    @if (auth()->user()->can('distribusi utama') || auth()->user()->can('distribusi cabang') || auth()->user()->can('lihat riwayat distribusi') || auth()->user()->hasRole('Admin'))
                        @php
                            $isDistributionActive = Route::is('distribution.*');
                        @endphp
                        <li class="nav-item dropdown {{ $isDistributionActive ? 'active show' : '' }}">
                            <a class="nav-link dropdown-toggle" href="#navbar-distribusi" data-bs-toggle="dropdown"
                                data-bs-auto-close="false" role="button" aria-expanded="{{ $isDistributionActive ? 'true' : 'false' }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <x-icons.truck class="w-5 h-5" />
                                </span>
                                <span class="nav-link-title">Distribusi Voucher</span>
                            </a>
                            <div class="dropdown-menu {{ $isDistributionActive ? 'show' : '' }}">
                                @if (auth()->user()->hasRole('Admin') || !auth()->user()->can('distribusi cabang'))
                                    @if (auth()->user()->can('distribusi utama') || auth()->user()->hasRole('Admin'))
                                        <a class="dropdown-item {{ Route::is('distribution.utama.*') ? 'active' : '' }}" href="{{ route('distribution.utama.index') }}">
                                            <x-icons.package class="w-4 h-4" />
                                            <span>Topup Kuota Voucher</span>
                                        </a>
                                    @endif
                                @endif
                                @if (auth()->user()->can('distribusi cabang'))
                                    <a class="dropdown-item {{ Route::is('distribution.cabang.*') ? 'active' : '' }}" href="{{ route('distribution.cabang.index') }}">
                                        <x-icons.branch class="w-4 h-4" />
                                        <span>Distribusi Utama</span>
                                    </a>
                                @endif
                                @if (auth()->user()->can('lihat riwayat distribusi'))
                                    <a class="dropdown-item {{ Route::is('distribution.history') ? 'active' : '' }}" href="{{ route('distribution.history') }}">
                                        <x-icons.clock class="w-4 h-4" />
                                        <span>Riwayat Distribusi</span>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endif
                @endif

                {{-- KELOMPOK 4: AGENT & PELANGGAN --}}
                @if (auth()->user()->can('lihat pelanggan'))
                    <li class="nav-item pt-3 pb-1 px-3">
                        <span class="text-uppercase fw-bold text-blue-200" style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.75;">
                            Agent & Mitra
                        </span>
                    </li>

                    @php
                        $isCustomerActive = request()->is('module-customer*');
                    @endphp
                    <li class="nav-item dropdown {{ $isCustomerActive ? 'active show' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-pelanggan" data-bs-toggle="dropdown"
                            data-bs-auto-close="false" role="button" aria-expanded="{{ $isCustomerActive ? 'true' : 'false' }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <x-icons.users class="w-5 h-5" />
                            </span>
                            <span class="nav-link-title">Agent</span>
                        </a>
                        <div class="dropdown-menu {{ $isCustomerActive ? 'show' : '' }}">
                            <a class="dropdown-item {{ request()->is('module-customer/customers*') ? 'active' : '' }}" href="{{ route('customer.index') }}">
                                <x-icons.users class="w-4 h-4" />
                                <span>Data Agent</span>
                            </a>
                            @role('Admin')
                                <a class="dropdown-item {{ request()->is('module-customer/types*') ? 'active' : '' }}" href="{{ route('costumer.type.index') }}">
                                    <x-icons.shield class="w-4 h-4" />
                                    <span>Tipe Agent</span>
                                </a>
                                <a class="dropdown-item {{ request()->is('module-customer/status*') ? 'active' : '' }}" href="{{ route('costumer.status.index') }}">
                                    <x-icons.check class="w-4 h-4" />
                                    <span>Status Agent</span>
                                </a>
                            @endrole
                        </div>
                    </li>
                @endif

                {{-- KELOMPOK 5: PENGATURAN SISTEM --}}
                @role('Admin')
                    <li class="nav-item pt-3 pb-1 px-3">
                        <span class="text-uppercase fw-bold text-blue-200" style="font-size: 0.65rem; letter-spacing: 0.08em; opacity: 0.75;">
                            Pengaturan Sistem
                        </span>
                    </li>

                    @php
                        $isSettingsActive = Route::is('user.*') || Route::is('roles.*') || Route::is('usaha.*');
                    @endphp
                    <li class="nav-item dropdown {{ $isSettingsActive ? 'active show' : '' }}">
                        <a class="nav-link dropdown-toggle" href="#navbar-pengaturan" data-bs-toggle="dropdown"
                            data-bs-auto-close="false" role="button" aria-expanded="{{ $isSettingsActive ? 'true' : 'false' }}">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <x-icons.settings class="w-5 h-5" />
                            </span>
                            <span class="nav-link-title">Pengaturan</span>
                        </a>
                        <div class="dropdown-menu {{ $isSettingsActive ? 'show' : '' }}">
                            <a class="dropdown-item {{ Route::is('user.*') ? 'active' : '' }}" href="{{ route('user.index') }}">
                                <x-icons.users class="w-4 h-4" />
                                <span>Manajemen User</span>
                            </a>
                            <a class="dropdown-item {{ Route::is('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                                <x-icons.shield class="w-4 h-4" />
                                <span>Role & Hak Akses</span>
                            </a>
                            <a class="dropdown-item {{ Route::is('usaha.*') ? 'active' : '' }}" href="{{ route('usaha.index') }}">
                                <x-icons.settings class="w-4 h-4" />
                                <span>Profil Toko</span>
                            </a>
                        </div>
                    </li>
                @endrole

                {{-- Keluar Sistem --}}
                <li class="nav-item mt-4 pt-3" style="border-top: 1px solid rgba(255, 255, 255, 0.12);">
                    <a class="nav-link text-rose-300 hover:text-white" href="{{ route('logout') }}">
                        <span class="nav-link-icon d-md-none d-lg-inline-block text-rose-400">
                            <x-icons.logout class="w-5 h-5" />
                        </span>
                        <span class="nav-link-title fw-bold">Keluar Sistem</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>