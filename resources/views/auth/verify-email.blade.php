<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar correo - carsTUmotor</title>
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
            min-height: 100vh;
        }

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

        .page {
            padding: 3.2rem 2rem 4rem;
            display: flex;
            justify-content: center;
        }

        .wrap {
            width: 100%;
            max-width: 1100px;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 1.6rem;
            align-items: stretch;
        }

        .panel {
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background: #fff;
        }

        .left {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 55%, #0f3460 100%);
            color: #fff;
            position: relative;
        }

        .left::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -20%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.18) 0%, transparent 70%);
            border-radius: 50%;
        }

        .left::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -15%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(255, 159, 28, 0.12) 0%, transparent 70%);
            border-radius: 50%;
        }

        .left-inner {
            position: relative;
            z-index: 2;
            padding: 2.2rem;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 2rem;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 999px;
            padding: 0.4rem 0.75rem;
            font-weight: 700;
            color: rgba(255,255,255,0.9);
            width: fit-content;
        }

        .left h1 {
            font-size: 2.4rem;
            line-height: 1.15;
            font-weight: 900;
            margin-top: 1rem;
            background: linear-gradient(135deg, #fff, #ff9f1c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .left p {
            color: rgba(255,255,255,0.82);
            max-width: 520px;
        }

        .benefits {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.7rem;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.8rem 0.9rem;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 14px;
        }

        .benefit i {
            color: #ff9f1c;
        }

        .right {
            padding: 2.2rem;
        }

        .form-title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #1a1a2e;
            margin-bottom: 0.35rem;
        }

        .form-subtitle {
            color: #666;
            margin-bottom: 1.2rem;
        }

        .body-text {
            color: #555;
            line-height: 1.75;
            margin-bottom: 1.1rem;
            font-size: 0.98rem;
        }

        .email-hint {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.75rem;
            border-radius: 12px;
            background: #f6f6f6;
            border: 1px solid rgba(0,0,0,0.08);
            font-weight: 800;
            color: #1a1a2e;
            font-size: 0.92rem;
            margin-bottom: 1.2rem;
        }

        .email-hint i {
            color: #ff6b35;
        }

        form {
            display: grid;
            gap: 1rem;
        }

        .submit {
            padding: 0.95rem 1.2rem;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            font-weight: 900;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(255, 107, 53, 0.22);
        }

        .submit.secondary {
            background: #fff;
            color: #1a1a2e;
            border: 1px solid rgba(0,0,0,0.12);
        }

        .submit.secondary:hover {
            border-color: rgba(255, 107, 53, 0.45);
            box-shadow: 0 8px 22px rgba(0,0,0,0.06);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin: 0.35rem 0;
            color: #888;
            font-weight: 800;
        }

        .divider::before,
        .divider::after {
            content: '';
            height: 1px;
            background: rgba(0,0,0,0.10);
            flex: 1;
        }

        .note {
            background: #fff7ed;
            border: 1px solid rgba(255, 107, 53, 0.22);
            border-radius: 12px;
            padding: 0.9rem;
            color: #7c2d12;
            font-weight: 700;
            font-size: 0.95rem;
            display: flex;
            gap: 0.65rem;
            align-items: flex-start;
        }

        .note.ok {
            background: #ecfdf5;
            border-color: rgba(16, 185, 129, 0.28);
            color: #065f46;
        }

        .note.ok i {
            color: #10b981;
            margin-top: 2px;
        }

        .link {
            color: #ff6b35;
            font-weight: 900;
            text-decoration: none;
        }

        .link:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
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

        @media (max-width: 980px) {
            .wrap {
                grid-template-columns: 1fr;
            }
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

            .page {
                padding: 2.2rem 1.1rem 3.2rem;
            }

            .left-inner,
            .right {
                padding: 1.6rem;
            }

            .left h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    @include('partials.site-nav')

    <main class="page">
        <div class="wrap">
            <section class="panel left">
                <div class="left-inner">
                    <div>
                        <div class="tag"><i class="fas fa-envelope-circle-check"></i> Un paso más</div>
                        <h1>Confirmá tu correo</h1>
                        <p>Por seguridad necesitamos verificar que el email sea tuyo. Revisá la bandeja de entrada y la carpeta de spam.</p>
                    </div>

                    <div class="benefits">
                        <div class="benefit"><i class="fas fa-link"></i> Abrí el enlace que te enviamos</div>
                        <div class="benefit"><i class="fas fa-rotate"></i> Si no llega, podés reenviar el correo</div>
                        <div class="benefit"><i class="fas fa-car-side"></i> Después explorá el catálogo con tu cuenta</div>
                    </div>
                </div>
            </section>

            <section class="panel right">
                <div class="form-title">Verificá tu correo</div>
                <div class="form-subtitle">Te enviamos un enlace de verificación</div>

                <div class="email-hint">
                    <i class="fas fa-at"></i>
                    <span>{{ auth()->user()->email }}</span>
                </div>

                <p class="body-text">
                    Antes de continuar, hacé clic en el enlace del correo que te mandamos al registrarte. Si no ves el mensaje, esperá unos minutos o pedí un reenvío.
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="note ok" style="margin-bottom: 1rem;" role="status">
                        <i class="fas fa-paper-plane"></i>
                        <div>Te enviamos un nuevo enlace de verificación. Revisá tu correo.</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button class="submit" type="submit">
                        <i class="fas fa-paper-plane"></i> Reenviar correo de verificación
                    </button>
                </form>

                <div class="divider">o</div>

                <a class="submit secondary" href="/catalogo" style="text-decoration: none;">
                    <i class="fas fa-layer-group"></i> Ir al catálogo
                </a>

                <p class="body-text" style="margin-top: 1rem; margin-bottom: 0;">
                    ¿Usaste otro correo? Podés <a class="link" href="/">volver al inicio</a> o cerrar sesión y registrarte de nuevo.
                </p>

                <form method="POST" action="{{ route('logout') }}" style="margin-top: 0.75rem;">
                    @csrf
                    <button class="submit secondary" type="submit">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                    </button>
                </form>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 carsTUmotor. Todos los derechos reservados.</p>
        <p>Términos de servicio | Política de privacidad | Contáctanos</p>
    </footer>
</body>
</html>
