<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="@yield('robots', 'noindex, nofollow')">
    <meta name="description" content="@yield('description', 'Hệ thống nội bộ MKT - Indochine Real Estate')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ asset('') }}">
    <title>@yield('title', 'Indochine MKT')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('account/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ asset('account/css/theme.min.css?v=1.0') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @yield('css')
</head>
<body class="bg-light">
    <main id="content" role="main" class="main">
        @yield('content')
    </main>
    <script src="{{ asset('account/js/vendor.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('js')
</body>
</html>
