<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Psi</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    @include('layouts.partials.header')
    <div class="layoutSidenav">
        @include('layouts.partials.sidebar')
        <div class="layoutSidenav_content">
            @yield('content')
            @include('layouts.partials.footer')
        </div>
    </div>
</body>
</html>