<!doctype html>

<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<meta http-equiv="X-UA-Compatible" content="ie=edge" />
	<title>@yield('title') | {{ config('app.name') }}</title>
	<!-- CSS files -->
	<link href="{{asset('')}}css/tabler.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-flags.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-socials.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-payments.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-vendors.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/tabler-marketing.min.css?1738096685" rel="stylesheet" />
	<link href="{{asset('')}}css/demo.min.css?1738096685" rel="stylesheet" />
	<style>
		@import url('https://rsms.me/inter/inter.css');
	</style>
    @stack('css')
</head>

<body>
	<script src="{{asset('')}}js/demo-theme.min.js?1738096685"></script>
	<div class="page">
		<!-- Sidebar -->
        @include('components.sidebar')
		<div class="page-wrapper">
			<!-- Page header -->
			<div class="page-header d-print-none">
				<div class="container-fluid">
					<div class="row g-2 align-items-center">
						<div class="col">
							<!-- Page pre-title -->
							<div class="page-pretitle">
								Pages
							</div>
							<h2 class="page-title">
								@yield('title')
							</h2>
						</div>
						<!-- Page title actions -->
						<div class="col-auto ms-auto d-print-none">
							<div class="btn-list">
								<a href="#" class="btn btn-outline-primary btn-5 d-none d-sm-inline-block">
									<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-user-scan"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 9a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M4 8v-2a2 2 0 0 1 2 -2h2" /><path d="M4 16v2a2 2 0 0 0 2 2h2" /><path d="M16 4h2a2 2 0 0 1 2 2v2" /><path d="M16 20h2a2 2 0 0 0 2 -2v-2" /><path d="M8 16a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2" /></svg>
									{{ Auth::user()->name }}
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- Page body -->
			<div class="page-body">
				<div class="container-fluid">
					@yield('content')
				</div>
			</div>
			<footer class="footer footer-transparent d-print-none">
				<div class="container-xl">
					<div class="row text-center align-items-center flex-row-reverse">
						<div class="col-12 col-lg-auto mt-3 mt-lg-0">
							<ul class="list-inline list-inline-dots mb-0">
								<li class="list-inline-item">
									Copyright &copy; {{ date('Y') }}
                                    {{ config('app.name') }}
									All rights reserved.
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

    @stack('modal')

	<!-- Libs JS -->
	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="{{asset('')}}libs/apexcharts/dist/apexcharts.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/jsvectormap.min.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world.js?1738096685" defer></script>
	<script src="{{asset('')}}libs/jsvectormap/dist/maps/world-merc.js?1738096685" defer></script>
	<!-- Tabler Core -->
	<script src="{{asset('')}}js/tabler.min.js?1738096685" defer></script>
	<script src="{{asset('')}}js/demo.min.js?1738096685" defer></script>
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