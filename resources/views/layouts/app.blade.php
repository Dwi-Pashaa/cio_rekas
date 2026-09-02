<!doctype html>
<html lang="id">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>@yield('title', 'POS') | {{ config('app.name', 'CIO Rekas') }}</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tabler Core CSS -->
    <link href="{{ asset('css/tabler.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/tabler-flags.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/tabler-payments.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/tabler-vendors.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/demo.min.css') }}" rel="stylesheet" />

    <!-- Tailwind CSS with Preflight Disabled -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        blue: {
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            200: '#BFDBFE',
                            300: '#93C5FD',
                            400: '#60A5FA',
                            500: '#3B82F6',
                            600: '#2563EB',
                            700: '#1D4ED8',
                            800: '#1E40AF',
                            900: '#1E3A8A',
                            950: '#172554',
                        },
                        slate: {
                            50: '#F8FAFC',
                            100: '#F1F5F9',
                            200: '#E2E8F0',
                            300: '#CBD5E1',
                            400: '#94A3B8',
                            500: '#64748B',
                            600: '#475569',
                            700: '#334155',
                            800: '#1E293B',
                            900: '#0F172A',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif !important;
            background-color: #F8FAFC !important;
            color: #0F172A !important;
        }

        .page {
            background-color: #F8FAFC !important;
        }

        /* ===================================================
           --- SOLID PROFESSIONAL BLUE SIDEBAR ---
           =================================================== */
        .navbar-vertical {
            background: linear-gradient(180deg, #1E40AF 0%, #172554 100%) !important;
            border-right: 1px solid #1E3A8A !important;
            box-shadow: 4px 0 16px rgba(15, 23, 42, 0.08) !important;
        }

        /* Fix: prevent Tailwind .collapse from hiding sidebar */
        .navbar-vertical .navbar-collapse {
            visibility: visible !important;
        }

        @media (min-width: 992px) {
            .navbar-vertical.navbar-expand-lg {
                width: 17.5rem !important; /* 280px */
            }

            .navbar-vertical.navbar-expand-lg ~ .page-wrapper {
                margin-left: 17.5rem !important;
            }

            .navbar-expand-lg .navbar-collapse {
                display: flex !important;
                visibility: visible !important;
                flex-direction: column !important;
                align-items: stretch !important;
            }
        }

        /* Group Heading Label */
        .sidebar-heading {
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.08em !important;
            color: #93C5FD !important;
            padding: 0.75rem 1rem 0.25rem 1rem !important;
            opacity: 0.85;
            display: flex;
            align-items: center;
        }

        /* Nav Link Base Style */
        .navbar-vertical .nav-link {
            color: #DBEAFE !important;
            border-radius: 0.75rem !important;
            margin: 2px 10px !important;
            padding: 0.6rem 0.85rem !important;
            font-weight: 500 !important;
            font-size: 0.875rem !important;
            transition: all 0.15s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }

        .navbar-vertical .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.12) !important;
            color: #FFFFFF !important;
        }

        /* Standalone Active Nav Link (Crisp White Pill with Bold Blue Text) */
        .navbar-vertical .nav-item.active:not(.dropdown) > .nav-link,
        .navbar-vertical .nav-link.active:not(.dropdown-toggle) {
            background-color: #FFFFFF !important;
            color: #1E40AF !important;
            font-weight: 700 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18) !important;
        }

        .navbar-vertical .nav-item.active:not(.dropdown) > .nav-link .nav-link-icon,
        .navbar-vertical .nav-link.active:not(.dropdown-toggle) .nav-link-icon {
            color: #1E40AF !important;
        }

        /* Active Dropdown Parent (Gentle Translucent Highlight) */
        .navbar-vertical .nav-item.dropdown.active > .nav-link {
            background-color: rgba(255, 255, 255, 0.12) !important;
            color: #FFFFFF !important;
            font-weight: 600 !important;
        }

        .navbar-vertical .nav-item.dropdown.active > .nav-link .nav-link-icon {
            color: #FFFFFF !important;
        }

        /* Remove default Tabler left active border bars */
        .navbar-vertical .nav-link::before,
        .navbar-vertical .nav-item::before {
            display: none !important;
        }

        /* Nav Icon */
        .navbar-vertical .nav-link-icon {
            color: #93C5FD !important;
            margin-right: 0.75rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: color 0.15s ease;
        }

        .navbar-vertical .nav-link:hover .nav-link-icon {
            color: #FFFFFF !important;
        }

        /* Nested Dropdown Menu on Dark Blue */
        .navbar-vertical .dropdown-menu {
            border: 0 !important;
            border-radius: 0.75rem !important;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2) !important;
            margin: 4px 10px 8px 10px !important;
            background-color: rgba(15, 23, 42, 0.45) !important;
            padding: 0.35rem !important;
        }

        .navbar-vertical .dropdown-item {
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.85rem !important;
            font-size: 0.825rem !important;
            color: #DBEAFE !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.5rem !important;
            transition: all 0.15s ease !important;
        }

        .navbar-vertical .dropdown-item svg {
            color: #93C5FD !important;
            flex-shrink: 0 !important;
            transition: color 0.15s ease !important;
        }

        .navbar-vertical .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.12) !important;
            color: #FFFFFF !important;
        }

        .navbar-vertical .dropdown-item:hover svg {
            color: #FFFFFF !important;
        }

        /* Active Sub-item: Crisp White Pill with Bold Blue Text */
        .navbar-vertical .dropdown-item.active {
            background-color: #FFFFFF !important;
            color: #1E40AF !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
        }

        .navbar-vertical .dropdown-item.active svg {
            color: #1E40AF !important;
        }

        .navbar-vertical .nav-link.dropdown-toggle::after {
            margin-left: auto !important;
            border: solid #93C5FD !important;
            border-width: 0 2px 2px 0 !important;
            display: inline-block !important;
            padding: 2.5px !important;
            transform: rotate(45deg) !important;
            transition: transform 0.2s ease !important;
        }

        .navbar-vertical .nav-link.dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(-135deg) !important;
        }

        /* Eliminate top gap completely for seamless flush navbar */
        .page-wrapper {
            padding-top: 0 !important;
            margin-top: 0 !important;
        }

        /* Top Header (Flush with viewport top & sticky) */
        .page-header {
            background-color: #FFFFFF !important;
            border-bottom: 1px solid #E2E8F0 !important;
            padding-top: 0.85rem !important;
            padding-bottom: 0.85rem !important;
            margin-top: 0 !important;
            margin-bottom: 1.5rem !important;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>

    @stack('css')
</head>

<body class="antialiased">
    <div class="page">
        <!-- Sidebar Navigation -->
        @include('components.sidebar')

        <div class="page-wrapper">
            <!-- Header Bar -->
            <div class="page-header d-print-none">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        {{-- Left: Pretitle & Title --}}
                        <div class="d-flex flex-column justify-content-center">
                            @if(trim($__env->yieldContent('pretitle')))
                                <div class="text-xs font-semibold text-blue-600 text-uppercase tracking-wider" style="font-size: 0.7rem; letter-spacing: 0.08em; line-height: 1.2;">
                                    @yield('pretitle')
                                </div>
                            @endif
                            <h2 class="fs-4 font-bold text-slate-900 m-0 tracking-tight lh-sm">
                                @yield('title')
                            </h2>
                        </div>

                        <!-- Right: Action Area & User Profile -->
                        <div class="d-flex align-items-center gap-2.5 ms-auto">
                            @yield('header-actions')

                            {{-- User Profile Pill (Clean, Properly Sized & Aligned) --}}
                            <div class="nav-item dropdown">
                                <a href="#" class="nav-link d-flex align-items-center gap-2 p-1 pe-2.5 rounded-pill bg-slate-50 border border-slate-200 text-decoration-none" 
                                   data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                    <div class="rounded-circle bg-blue-600 text-white fw-bold d-flex align-items-center justify-content-center shrink-0 shadow-xs" 
                                         style="width: 32px; height: 32px; font-size: 0.825rem; line-height: 1;">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="d-none d-md-flex flex-column text-start me-1">
                                        <span class="fw-bold text-slate-800 lh-sm" style="font-size: 0.825rem;">{{ Auth::user()->name }}</span>
                                        <span class="text-muted text-capitalize" style="font-size: 0.68rem; line-height: 1.1;">
                                            {{ Auth::user()->getRoleNames()[0] ?? 'Kasir' }}
                                        </span>
                                    </div>
                                    <x-icons.chevron-down class="w-3.5 h-3.5 text-slate-400" />
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-lg rounded-3 border-0 py-2 mt-2" style="min-width: 200px;">
                                    <div class="px-3 py-2 border-bottom border-slate-100 mb-1">
                                        <div class="fw-bold text-dark small">{{ Auth::user()->name }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ Auth::user()->email }}</div>
                                        <span class="badge bg-blue-50 text-blue-700 border border-blue-200 rounded-pill px-2 py-0.5 mt-1" style="font-size: 0.65rem;">
                                            {{ Auth::user()->getRoleNames()[0] ?? 'Kasir' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('logout') }}" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                        <x-icons.logout class="w-4 h-4 text-danger" />
                                        <span class="small fw-semibold">Keluar Sistem</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Body -->
            <div class="page-body">
                <div class="container-fluid px-4">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none py-3 border-top border-slate-200 mt-auto bg-white">
                <div class="container-fluid px-4">
                    <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between text-xs text-slate-500 gap-2">
                        <div>
                            Hak Cipta &copy; {{ date('Y') }} <span class="fw-bold text-slate-700">{{ config('app.name', 'CIO Rekas') }}</span>. Seluruh hak cipta dilindungi.
                        </div>
                        <div class="text-slate-400">
                            Versi 2.0 &bull; Modern POS System
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @stack('modal')

    <!-- Libs JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('libs/apexcharts/dist/apexcharts.min.js') }}" defer></script>
    <script src="{{ asset('js/tabler.min.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    @stack('js')
</body>

</html>