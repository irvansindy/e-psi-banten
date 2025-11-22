<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'e-Psi'}}</title>
    <link href="{{ asset('assets/css/cdn/simple-datatable.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <script src="{{ asset('assets/js/cdn/font-awesome.js') }}" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    @include('layouts.partials.header')
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            @include('layouts.partials.sidebar')
        </div>
        <div id="layoutSidenav_content">
            @yield('content')
            @include('layouts.partials.footer')
        </div>
    </div>
    <script src="{{ asset('assets/js/cdn/bootstrap-bundle.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/cdn/chart.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/assets/demo/chart-area-demo.js') }}"></script>
    <script src="{{ asset('assets/assets/demo/chart-bar-demo.js') }}"></script>
    <script src="{{ asset('assets/js/cdn/simple-datatable.js') }}" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/datatables-simple-demo.js') }}"></script>
    <script src="{{ asset('assets/js/cdn/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/cdn/moment.js') }}"></script>
    @stack('scripts')
</body>
</html>