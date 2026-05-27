@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('breadcrumbs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Inicio</a></li>
            <li class="breadcrumb-item active">Mi Perfil</li>
        </ol>
    </nav>
@endsection

@section('page_title', 'Mi Perfil')
@section('page_subtitle', 'Visualiza tu información personal')

@section('content')
    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2>{{ $user->name }}</h2>
                <p class="text-muted">{{ Auth::user()->role }}</p>
            </div>

            <div class="profile-body">
                <div class="profile-section">
                    <h3>Información Personal</h3>
                    <div class="profile-grid">
                        <div class="profile-item">
                            <label>Nombre</label>
                            <p>{{ $user->name }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Correo Electrónico</label>
                            <p>{{ $user->email }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Teléfono</label>
                            <p>{{ $user->phone ?? 'No especificado' }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Dirección</label>
                            <p>{{ $user->address ?? 'No especificada' }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Documento</label>
                            <p>{{ $user->document ?? 'No especificado' }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Rol</label>
                            <p>
                                <span class="badge badge-primary">{{ $user->role }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Actividad</h3>
                    <div class="profile-grid">
                        <div class="profile-item">
                            <label>Miembro desde</label>
                            <p>{{ $user->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Última actualización</label>
                            <p>{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="profile-item">
                            <label>Email Verificado</label>
                            <p>
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success">Verificado</span>
                                @else
                                    <span class="badge badge-warning">Pendiente</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Perfil
                </a>
                <a href="{{ route('profile.changePassword') }}" class="btn btn-secondary">
                    <i class="fas fa-lock"></i> Cambiar Contraseña
                </a>
            </div>
        </div>
    </div>

    <style>
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .profile-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .profile-header {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .profile-avatar {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .profile-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        .profile-body {
            padding: 2rem;
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .profile-section h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .profile-item {
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }

        .profile-item label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-item p {
            margin: 0;
            color: #333;
            font-size: 1rem;
        }

        .profile-actions {
            padding: 2rem;
            background: #f9f9f9;
            display: flex;
            gap: 1rem;
            justify-content: center;
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

        .badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-primary {
            background: #667eea;
            color: white;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .text-muted {
            color: #999;
            font-size: 0.95rem;
        }
    </style>
@endsection
