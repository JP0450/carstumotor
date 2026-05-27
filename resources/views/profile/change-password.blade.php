@extends('layouts.admin')

@section('title', 'Cambiar Contraseña')

@section('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Mi Perfil</a></li>
            <li class="breadcrumb-item active">Cambiar Contraseña</li>
        </ol>
    </nav>
@endsection

@section('page_title', 'Cambiar Contraseña')
@section('page_subtitle', 'Actualiza tu contraseña de forma segura')

@section('content')
    <div class="form-container">
        <div class="form-card">
            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                <p>Por tu seguridad, debes proporcionar tu contraseña actual para establecer una nueva.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <h4>Errores en el formulario:</h4>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.updatePassword') }}">
                @csrf

                <div class="form-group">
                    <label for="current_password" class="form-label">Contraseña Actual</label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="form-control @error('current_password') is-invalid @enderror"
                        required
                        autocomplete="current-password"
                    >
                    @error('current_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="divider"></div>

                <div class="form-group">
                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        class="form-control @error('new_password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                    >
                    <small class="form-helper">Mínimo 8 caracteres</small>
                    @error('new_password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <input
                        type="password"
                        id="new_password_confirmation"
                        name="new_password_confirmation"
                        class="form-control @error('new_password_confirmation') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                    >
                    @error('new_password_confirmation')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="password-requirements">
                    <h4>Requisitos de seguridad:</h4>
                    <ul>
                        <li><i class="fas fa-check"></i> Mínimo 8 caracteres</li>
                        <li><i class="fas fa-check"></i> Diferente a la contraseña actual</li>
                        <li><i class="fas fa-check"></i> Las confirmaciones deben coincidir</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-lock"></i> Cambiar Contraseña
                    </button>
                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 2rem;
        }

        .security-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 2rem;
            color: #004085;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .security-info i {
            font-size: 1.5rem;
            flex-shrink: 0;
            margin-top: 0.25rem;
        }

        .security-info p {
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-error {
            display: block;
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .form-helper {
            display: block;
            color: #666;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .divider {
            border-top: 2px solid #f0f0f0;
            margin: 2rem 0;
        }

        .password-requirements {
            background: #f9f9f9;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 1rem;
            margin: 1.5rem 0;
        }

        .password-requirements h4 {
            margin-top: 0;
            margin-bottom: 0.75rem;
            color: #333;
            font-size: 0.95rem;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.5rem;
            list-style: none;
        }

        .password-requirements li {
            margin-bottom: 0.5rem;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .password-requirements i {
            color: #28a745;
            font-size: 0.9rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .alert-danger h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
    </style>
@endsection
