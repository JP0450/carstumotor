<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administración') — carsTUmotor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/user-nav.css') }}">
    @include('layouts.partials.admin-theme-styles')
</head>
<body>
    @include('partials.site-nav')

    @if (session('success'))
        <div class="admin-main" style="padding-bottom: 0;">
            <div class="flash-banner">
                <div class="flash success" role="status">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        </div>
    @endif

    <header class="page-header">
        <div class="header-inner">
            <div class="breadcrumbs">
                @yield('breadcrumbs')
            </div>
            <h1 class="page-title">@yield('page_title')</h1>
            <p class="page-subtitle">@yield('page_subtitle')</p>
        </div>
    </header>

    <main class="admin-main">
        @yield('content')
    </main>

    <footer>
        <p>&copy; {{ date('Y') }} carsTUmotor — Panel de gestión</p>
    </footer>
</body>
</html>
