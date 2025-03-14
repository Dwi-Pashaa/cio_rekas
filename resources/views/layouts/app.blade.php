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