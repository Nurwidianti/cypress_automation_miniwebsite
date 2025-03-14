<!DOCTYPE html>
<!--
* CoreUI Free Laravel Bootstrap Admin Template
* @version v2.0.1
* @link https://coreui.io
* Copyright (c) 2020 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://coreui.io/license)
-->

<html lang="en">

<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="MIS">
    <!--<meta name="author" content="Łukasz Holeczek"> -->
    <meta http-equiv="X-Frame-Options" content="SAMEORIGIN">

    <title>MIS</title>
    {{-- <link rel="apple-touch-icon" sizes="57x57" href="assets/favicon/apple-icon-57x57.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="60x60" href="assets/favicon/apple-icon-60x60.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="72x72" href="assets/favicon/apple-icon-72x72.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="76x76" href="assets/favicon/apple-icon-76x76.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="114x114" href="assets/favicon/apple-icon-114x114.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="120x120" href="assets/favicon/apple-icon-120x120.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="144x144" href="assets/favicon/apple-icon-144x144.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="152x152" href="assets/favicon/apple-icon-152x152.png"> --}}
    {{-- <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-icon-180x180.png"> --}}
    {{-- <link rel="icon" type="image/png" sizes="192x192" href="assets/favicon/android-icon-192x192.png"> --}}
    {{-- <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png"> --}}
    {{-- <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png"> --}}
    {{-- <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png"> --}}
    {{-- <link rel="manifest" href="{{ asset('assets/favicon/manifest.json') }}"> --}}
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <!-- Icons-->
    <link href="{{ asset('css/free.min.css') }}" rel="stylesheet"> <!-- icons -->
    {{-- <link href="{{ asset('css/flag-icon.min.css') }}" rel="stylesheet"> <!-- icons --> --}}
    <!-- Main styles for this application-->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    @yield('css')
    <link href="{{ asset('css/buttons.dataTables.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/coreui.min.css') }}" rel="stylesheet">
    <!-- siswo start -->
    <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
    <!-- siswo end -->
</head>



<body class="c-app">
    <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-show" id="sidebar">

        @include('dashboard.shared.nav-builder')
        @include('sweetalert::alert')
        @include('dashboard.shared.header')

        <div class="c-body">

            <main class="c-main">

                @yield('content')

            </main>
            @include('dashboard.shared.footer')
        </div>
    </div>

    <!-- CoreUI and necessary plugins-->
    <script src="{{ asset('js/coreui.bundle.min.js') }}"></script>
    <script src="{{ asset('js/accordion.js') }}"></script>
    <script src="{{ asset('js/coreui-utils.js') }}"></script>
    <script src="{{ asset('js/jquery.datatables.js') }}"></script>
    <script src="{{ asset('js/bootstrap.datatables.js') }}"></script>
     <!-- siswo start  -->
    <script src="{{ asset('js/select2.min.js') }}"></script>
     <!-- siswo end  -->
      <!-- yaqin start -->
    <script src="{{ asset('js/fungsi.js') }}"></script>
    <!-- yaqin end -->
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('js/helper9.js') }}"></script>
    @yield('javascript')


</body>

    @stack('scripts')
</html>
