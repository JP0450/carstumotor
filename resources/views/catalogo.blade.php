<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo - carsTUmotor</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/user-nav.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            background: #f8f8f8;
        }

        /* ==================== NAVBAR ==================== */
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 2rem;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo i {
            color: #ff6b35;
            font-size: 2rem;
        }

        .logo span {
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-login {
            background: transparent;
            color: #fff;
            border: 2px solid #ff6b35;
        }

        .btn-login:hover {
            background: #ff6b35;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-register {
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            border: 2px solid transparent;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 53, 0.4);
        }

        /* ==================== HEADER ==================== */
        .page-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            padding: 3.5rem 2rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -10%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.18) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -60%;
            left: -10%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(255, 159, 28, 0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .breadcrumbs {
            display: inline-flex;
            gap: 0.5rem;
            align-items: center;
            font-size: 0.95rem;
            color: rgba(255,255,255,0.75);
            margin-bottom: 0.75rem;
        }

        .breadcrumbs a {
            color: rgba(255,255,255,0.9);
            text-decoration: none;
        }

        .breadcrumbs a:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .page-title {
            font-size: 2.6rem;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 0.6rem;
            background: linear-gradient(135deg, #fff, #ff9f1c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: rgba(255,255,255,0.78);
            max-width: 720px;
        }

        .catalog-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1.4rem;
        }

        .btn-primary,
        .btn-secondary {
            padding: 0.85rem 1.3rem;
            border-radius: 10px;
            font-weight: 700;
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.28);
        }

        .btn-primary:hover,
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        /* ==================== FILTER BAR (ESTÉTICO) ==================== */
        .filters-wrap {
            max-width: 1200px;
            margin: -1.5rem auto 0;
            padding: 0 2rem;
        }

        .filters {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0,0,0,0.06);
            padding: 1rem;
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr auto;
            gap: 0.8rem;
            align-items: center;
        }

        .input {
            display: flex;
            gap: 0.6rem;
            align-items: center;
            background: #f6f6f6;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 10px;
            padding: 0.8rem 0.9rem;
            color: #333;
        }

        .input i {
            color: #ff6b35;
        }

        .input input,
        .input select {
            border: none;
            outline: none;
            background: transparent;
            width: 100%;
            font-size: 1rem;
        }

        .btn-action {
            padding: 0.85rem 1.2rem;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            justify-content: center;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.25);
        }

        /* ==================== CATALOG GRID ==================== */
        .catalog {
            max-width: 1200px;
            margin: 1.8rem auto 0;
            padding: 0 2rem 4rem;
        }

        .catalog-top {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 1rem;
            margin: 1.5rem 0 1rem;
        }

        .catalog-top h2 {
            font-size: 1.6rem;
            color: #1a1a2e;
        }

        .catalog-top p {
            color: #666;
            font-size: 0.98rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.4rem;
        }

        .card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0,0,0,0.06);
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 14px 36px rgba(255, 107, 53, 0.14);
            border-color: rgba(255, 107, 53, 0.35);
        }

        .media {
            position: relative;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
            aspect-ratio: 16 / 10;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.8);
        }

        .badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.18);
            color: #fff;
            padding: 0.35rem 0.6rem;
            border-radius: 999px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            backdrop-filter: blur(10px);
        }

        .media i {
            font-size: 2.8rem;
            opacity: 0.9;
        }

        .content {
            padding: 1rem 1rem 1.1rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            flex: 1;
        }

        .title-row {
            display: flex;
            justify-content: space-between;
            gap: 0.8rem;
            align-items: baseline;
        }

        .title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #1a1a2e;
        }

        .year {
            color: #666;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .meta {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.6rem;
        }

        .meta-item {
            background: #f6f6f6;
            border: 1px solid rgba(0,0,0,0.06);
            padding: 0.55rem 0.6rem;
            border-radius: 12px;
            text-align: center;
            font-size: 0.9rem;
            color: #444;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.35rem;
        }

        .meta-item i {
            color: #ff6b35;
        }

        .card-actions {
            margin-top: auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.7rem;
        }

        .btn-ghost {
            padding: 0.8rem 0.9rem;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.12);
            background: #fff;
            color: #1a1a2e;
            font-weight: 800;
            text-decoration: none;
            text-align: center;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .btn-ghost:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 107, 53, 0.5);
            box-shadow: 0 10px 26px rgba(0,0,0,0.06);
        }

        .btn-solid {
            padding: 0.8rem 0.9rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            font-weight: 900;
            text-decoration: none;
            text-align: center;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
        }

        .btn-solid:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(255, 107, 53, 0.22);
        }

        footer {
            background: #0f0f1e;
            color: #999;
            padding: 2rem;
            text-align: center;
            font-size: 0.9rem;
        }

        footer p {
            margin: 0.5rem 0;
        }

        @media (max-width: 900px) {
            .filters {
                grid-template-columns: 1fr;
            }

            .catalog-top {
                flex-direction: column;
                align-items: start;
            }
        }

        .flash-banner {
            max-width: 1200px;
            margin: 1rem auto 0;
            padding: 0 2rem;
        }

        .flash {
            border-radius: 14px;
            padding: 0.95rem 1.1rem;
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            font-weight: 800;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 8px 22px rgba(0,0,0,0.06);
        }

        .flash.success {
            background: #ecfdf5;
            color: #065f46;
            border-color: rgba(16, 185, 129, 0.25);
        }

        .flash.success i {
            color: #10b981;
            margin-top: 2px;
        }

        .flash.info {
            background: #eff6ff;
            color: #1e3a8a;
            border-color: rgba(59, 130, 246, 0.25);
        }

        .flash.info i {
            color: #3b82f6;
            margin-top: 2px;
        }

        .flash.error {
            background: #fef2f2;
            color: #991b1b;
            border-color: rgba(239, 68, 68, 0.25);
        }

        .flash.error i {
            color: #ef4444;
            margin-top: 2px;
        }

        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .page-title {
                font-size: 2rem;
            }

            .filters-wrap {
                padding: 0 1.1rem;
            }

            .catalog {
                padding: 0 1.1rem 3.2rem;
            }
        }
    </style>
</head>
<body>
    @include('partials.site-nav')

    @if (session('verified'))
        <div class="flash-banner">
            <div class="flash success" role="status">
                <i class="fas fa-circle-check"></i>
                <span>Correo verificado correctamente. Ya podés seguir usando carsTUmotor con tu cuenta.</span>
            </div>
        </div>
    @endif

    @if (session('status') === 'verification-link-sent')
        <div class="flash-banner">
            <div class="flash info" role="status">
                <i class="fas fa-paper-plane"></i>
                <span>Te enviamos un nuevo enlace de verificación al correo que registraste.</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="flash-banner">
            <div class="flash error" role="alert">
                <i class="fas fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <header class="page-header">
        <div class="header-inner">
            <div class="breadcrumbs">
                <a href="/">Inicio</a>
                <span>/</span>
                <span>Catálogo</span>
            </div>
            <h1 class="page-title">Catálogo de vehículos</h1>
            <p class="page-subtitle">
                @auth
                    Hola, {{ strtok(auth()->user()->name, ' ') }} — acá ves los vehículos disponibles. Usá los filtros para acotar la búsqueda.
                @else
                    Explorá nuestros vehículos disponibles y agendá tu asesoría personalizada.
                @endauth
            </p>

            @auth
                @php
                    $userRole = strtolower(trim(auth()->user()->role ?? ''));
                    $isAdminRole = in_array($userRole, [\App\Models\User::ROLE_JEFE, \App\Models\User::ROLE_CONTADOR], true);
                @endphp
                @if ($isAdminRole)
                    <div class="catalog-actions">
                        <a class="btn btn-secondary" href="{{ route('admin.vehiculos.index') }}">
                            <i class="fas fa-clipboard-list"></i> Ir al CRUD de vehículos
                        </a>
                        <a class="btn btn-primary" href="{{ route('admin.dashboard.contador') }}">
                            <i class="fas fa-chart-line"></i> Ver Dashboard
                        </a>
                    </div>
                @endif
            @endauth
        </div>
    </header>

    <div class="filters-wrap">
        <form class="filters" method="GET" action="/catalogo">
            <div class="input">
                <i class="fas fa-magnifying-glass"></i>
                <input type="text" name="marca" placeholder="Buscar por marca o modelo" value="{{ request('marca') }}">
            </div>
            <div class="input">
                <i class="fas fa-palette"></i>
                <select name="color">
                    <option value="">Todos los colores</option>
                    <option value="Rojo" {{ request('color') == 'Rojo' ? 'selected' : '' }}>Rojo</option>
                    <option value="Azul" {{ request('color') == 'Azul' ? 'selected' : '' }}>Azul</option>
                    <option value="Negro" {{ request('color') == 'Negro' ? 'selected' : '' }}>Negro</option>
                    <option value="Blanco" {{ request('color') == 'Blanco' ? 'selected' : '' }}>Blanco</option>
                    <option value="Gris" {{ request('color') == 'Gris' ? 'selected' : '' }}>Gris</option>
                    <option value="Plata" {{ request('color') == 'Plata' ? 'selected' : '' }}>Plata</option>
                </select>
            </div>
            <div class="input">
                <i class="fas fa-dollar-sign"></i>
                <input type="number" name="precio_max" placeholder="Precio máximo" value="{{ request('precio_max') }}">
            </div>
            <button type="submit" class="btn-action">
                <i class="fas fa-bolt"></i> Ver resultados
            </button>
        </form>
    </div>

    <main class="catalog" id="catalogo">
        <div class="catalog-top">
            <div>
                <h2>Vehículos destacados</h2>
                <p>Mostrando vehículos disponibles.</p>
            </div>
            <p>Resultados: {{ $vehiculos->count() }}</p>
        </div>

        <div class="grid">
            @forelse($vehiculos as $vehiculo)
            <article class="card">
                <div class="media">
                    @if($vehiculo->disponible)
                        <span class="badge"><i class="fas fa-check-circle"></i> Disponible</span>
                    @else
                        <span class="badge"><i class="fas fa-times-circle"></i> No disponible</span>
                    @endif
                    @if($vehiculo->imagen)
                        <img src="{{ $vehiculo->imagen }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}" style="width:100%;height:100%;object-fit:cover;">
                    @else
                        <i class="fas fa-car"></i>
                    @endif
                </div>
                <div class="content">
                    <div class="title-row">
                        <div class="title">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</div>
                        <div class="year">{{ $vehiculo->color }}</div>
                    </div>
                    <div class="meta">
                        <div class="meta-item"><i class="fas fa-door-open"></i> {{ $vehiculo->puertas }} pta</div>
                        <div class="meta-item"><i class="fas fa-gauge-high"></i> {{ $vehiculo->hp }} HP</div>
                        <div class="meta-item"><i class="fas fa-tag"></i> ${{ number_format($vehiculo->precio_cliente, 0, ',', '.') }}</div>
                    </div>
                    <div class="card-actions">
                        <a class="btn-solid" href="#"><i class="fas fa-eye"></i> Ver ficha</a>
                    </div>
                </div>
            </article>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; color: #666;">
                <i class="fas fa-car" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                <p>No se encontraron vehículos con los filtros seleccionados.</p>
            </div>
            @endforelse
        </div>
    </main>

    <footer>
        <p>&copy; 2024 carsTUmotor. Todos los derechos reservados.</p>
        <p>Términos de servicio | Política de privacidad | Contáctanos</p>
    </footer>
</body>
</html>
