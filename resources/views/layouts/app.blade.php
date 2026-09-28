<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'carsTUmotor')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="min-h-screen bg-zinc-50 text-slate-800 antialiased font-sans">
    @include('partials.site-nav')
    @include('partials.flash')
    <main>
        @yield('content')
    </main>
    <footer class="mt-12 bg-slate-950 text-zinc-400 py-8 text-center text-sm">
        <p>&copy; {{ date('Y') }} carsTUmotor. Todos los derechos reservados.</p>
        <p class="mt-1">Términos de servicio | Política de privacidad | Contáctanos</p>
    </footer>
    @stack('scripts')
</body>
</html>
