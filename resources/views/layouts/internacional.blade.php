<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ficha internacional') — carsTUmotor</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        html { background: #050505; }
        body.international-layout { min-height:100vh; margin:0; background:#050505; color:#f4f4f5; }
        .international-layout main { min-height:calc(100vh - 4rem); }
    </style>
</head>
<body class="international-layout">
    @include('partials.site-nav')
    @include('partials.flash')
    <main>
        @yield('content')
    </main>
</body>
</html>
