<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - ' . config('app.name', 'Ubiquiti UniFi Kenya'))</title>
    <meta name="description" content="@yield('meta_description', 'Admin management area.')">
    @php
        $marketCssVersion = @filemtime(public_path('assets/market.css')) ?: time();
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/market.css') }}?v={{ $marketCssVersion }}">
    @stack('head')
</head>
<body class="admin-body">
<main class="admin-root">
    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert error">
            {{ $errors->first() }}
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
