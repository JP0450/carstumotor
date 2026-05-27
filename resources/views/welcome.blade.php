<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>carsTUmotor - Tu comercializadora de autos de confianza</title>
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

        /* ==================== HERO SECTION ==================== */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            color: #fff;
            padding: 5rem 2rem;
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 159, 28, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }

        .hero-content {
            max-width: 800px;
            text-align: center;
            position: relative;
            z-index: 2;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #fff, #ff9f1c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: #b0b0b0;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .hero-description {
            font-size: 1.1rem;
            color: #d0d0d0;
            margin-bottom: 2.5rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            padding: 1rem 2.5rem;
            background: transparent;
            color: #fff;
            border: 2px solid #ff6b35;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #ff6b35;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
        }

        /* ==================== FEATURES SECTION ==================== */
        .features {
            padding: 4rem 2rem;
            background: #f8f8f8;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1a1a2e;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.15);
            border-color: #ff6b35;
        }

        .feature-icon {
            font-size: 3rem;
            color: #ff6b35;
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            color: #1a1a2e;
        }

        .feature-card p {
            color: #666;
            line-height: 1.8;
        }

        /* ==================== CTA SECTION ==================== */
        .cta {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 3rem 2rem;
            text-align: center;
            margin: 3rem 0;
        }

        .cta h2 {
            font-size: 2.2rem;
            margin-bottom: 1rem;
            color: #ff9f1c;
        }

        .cta p {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            color: #d0d0d0;
        }

        /* ==================== FOOTER ==================== */
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

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .btn-primary, .btn-secondary {
                width: 100%;
                justify-content: center;
            }

            nav {
                flex-direction: column;
                gap: 1rem;
            }

            .logo {
                font-size: 1.4rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .nav-buttons {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    @include('partials.site-nav')

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <h1>Bienvenido a carsTUmotor</h1>
            @auth
                <p class="hero-subtitle">Hola, {{ strtok(auth()->user()->name, ' ') }} — tu cuenta está activa. Explorá el catálogo o volvé cuando quieras.</p>
            @else
                <p class="hero-subtitle">La mejor plataforma para encontrar tu próximo vehículo</p>
            @endauth
            <p class="hero-description">
                Explora nuestra amplia selección de vehículos de calidad, con garantía de autenticidad 
                y las mejores opciones de financiamiento. Tu auto de ensueño está a un clic de distancia.
            </p>
            <div class="hero-buttons">
                <a class="btn-primary" href="/catalogo">
                    <i class="fas fa-search"></i> Explorar catálogo
                </a>
                <a class="btn-secondary" href="mailto:contacto@carstumotor.com">
                    <i class="fas fa-phone"></i> Contáctanos
                </a>
            </div>
        </div>
    </section>

    <!-- FEATURES SECTION -->
    <section class="features">
        <div class="features-container">
            <h2 class="section-title">¿Por qué elegir carsTUmotor?</h2>
            <p class="section-subtitle">Ofrecemos la mejor experiencia en la compra de vehículos</p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Autos verificados</h3>
                    <p>Todos nuestros vehículos pasan por inspecciones rigurosas para garantizar su calidad.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Transacciones seguras</h3>
                    <p>Protegemos tus datos con tecnología de encriptación de última generación.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Opciones de pago flexibles</h3>
                    <p>Múltiples opciones de financiamiento adaptadas a tu presupuesto.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Atención personalizada</h3>
                    <p>Nuestro equipo está disponible para ayudarte en cada paso del proceso.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Entrega rápida</h3>
                    <p>Recibe tu auto en el menor tiempo posible con nuestro servicio de entrega.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Garantía incluida</h3>
                    <p>Todos nuestros vehículos incluyen garantía extendida para tu tranquilidad.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta">
        <h2>¿Listo para encontrar tu auto ideal?</h2>
        <p>Comienza a explorar ahora mismo nuestro catálogo de vehículos disponibles</p>
        <a class="btn-primary" href="/catalogo">
            <i class="fas fa-arrow-right"></i> Explorar ahora
        </a>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2024 carsTUmotor. Todos los derechos reservados.</p>
        <p>Términos de servicio | Política de privacidad | Contáctanos</p>
    </footer>
</body>
</html>