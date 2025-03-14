<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, viewport-fit=cover"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>
      @yield('title') | {{ config('app.name') }}
    </title>
    <!-- CSS files -->
    <link href="{{asset('css/tabler.min.css?1738096682')}}" rel="stylesheet" />
    <link href="{{asset('css/tabler-flags.min.css?1738096682')}}" rel="stylesheet" />
    <link
      href="{{asset('css/tabler-socials.min.css?1738096682')}}"
      rel="stylesheet"
    />
    <link
      href="{{asset('css/tabler-payments.min.css?1738096682')}}"
      rel="stylesheet"
    />
    <link
      href="{{asset('css/tabler-vendors.min.css?1738096682')}}"
      rel="stylesheet"
    />
    <link
      href="{{asset('css/tabler-marketing.min.css?1738096682')}}"
      rel="stylesheet"
    />
    <link href="{{asset('css/demo.min.css?1738096682')}}" rel="stylesheet" />
    <style>
      @import url("https://rsms.me/inter/inter.css");
      .page {
        display: grid;
        place-items: center; /* Cara cepat untuk memusatkan elemen */
        min-height: 100vh;
      }
    </style>
  </head>
  <body class="d-flex flex-column">
    <script src="{{asset('')}}js/demo-theme.min.js?1738096682"></script>
    <div class="page">
      <div class="container container-tight py-4">
        <div class="text-center mb-4">
          <a href="{{route('login')}}" class="navbar-brand navbar-brand-autodark">
            <img src="{{asset('img/logo.png')}}" width="200" alt="">
          </a>
        </div>
        @if (session()->has('error'))
            @include('components.alert.danger')
        @endif

        @if (session()->has('warning'))
            @include('components.alert.warning')
        @endif
        <div class="card card-md">
          <div class="card-body">
            @yield('content')
          </div>
        </div>

        <div class="mt-5">
          <p class="text-muted text-center">
            &copy; Copyright {{ config('app.name') }} {{date('Y')}} - All Right Reserved
          </p>
        </div>
      </div>
    </div>
    <!-- Libs JS -->
    <!-- Tabler Core -->
    <script src="{{asset('js/tabler.min.js?1738096682')}}" defer></script>
    <script src="{{asset('js/demo.min.js?1738096682')}}" defer></script>
  </body>
</html>
