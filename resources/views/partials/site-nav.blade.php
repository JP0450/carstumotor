@php
    $roleLabels = [
        \App\Models\User::ROLE_EXTERNO => 'Cliente',
        \App\Models\User::ROLE_JEFE => 'Jefe',
        \App\Models\User::ROLE_NEGOCIOS => 'Negocios internacionales',
        \App\Models\User::ROLE_CONTADOR => 'Contador',
    ];
@endphp
<nav>
    <a href="/" class="logo">
        <i class="fas fa-car-side"></i>
        <span>carsTUmotor</span>
    </a>
    <div class="nav-buttons">
        @auth
            @php
                $u = auth()->user();
                $initial = mb_strtoupper(mb_substr($u->name ?: '?', 0, 1));
                $roleLabel = $u->role
                    ? ($roleLabels[$u->role] ?? ucfirst(str_replace('_', ' ', (string) $u->role)))
                    : 'Usuario';
            @endphp
            <details class="user-menu">
                <summary class="user-menu-trigger" aria-label="Menú de cuenta">
                    <span class="user-avatar">{{ $initial }}</span>
                    <span class="user-name">{{ $u->name }}</span>
                    <i class="fas fa-chevron-down user-chevron" aria-hidden="true"></i>
                </summary>
                <div class="user-menu-dropdown" role="menu">
                    <div class="user-menu-head">
                        <div class="user-menu-title">{{ $u->name }}</div>
                        <div class="user-menu-email">{{ $u->email }}</div>
                        <div class="user-menu-meta">
                            <span class="user-role-pill"><i class="fas fa-id-badge"></i> {{ $roleLabel }}</span>
                            @if($u->phone)
                                <span><i class="fas fa-phone" style="color:#ff6b35;"></i> {{ $u->phone }}</span>
                            @endif
                        </div>
                    </div>

                    @unless($u->hasVerifiedEmail())
                        <a class="user-menu-item warn" href="/email/verify" role="menuitem">
                            <i class="fas fa-envelope-circle-check"></i> Verificar correo
                        </a>
                    @endunless

                    <a class="user-menu-item" href="/catalogo" role="menuitem">
                        <i class="fas fa-layer-group"></i> Catálogo
                    </a>
                    @php
                        $currentRole = strtolower(trim($u->role ?? ''));
                        $isAdminRole = in_array($currentRole, [\App\Models\User::ROLE_JEFE, \App\Models\User::ROLE_CONTADOR], true);
                    @endphp

                    <a class="user-menu-item" href="{{ route('profile.show') }}" role="menuitem">
                        <i class="fas fa-user-circle"></i> Mi perfil
                    </a>
                    @if ($isAdminRole)
                        <a class="user-menu-item" href="{{ route('admin.vehiculos.index') }}" role="menuitem">
                            <i class="fas fa-clipboard-list"></i> Gestionar vehículos
                        </a>
                        <a class="user-menu-item" href="{{ route('admin.dashboard.contador') }}" role="menuitem">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    @endif
                    <a class="user-menu-item" href="/" role="menuitem">
                        <i class="fas fa-house"></i> Inicio
                    </a>

                    <div class="user-menu-logout">
                        <form method="POST" action="/logout">
                            @csrf
                            <button type="submit" role="menuitem">
                                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </div>
            </details>
        @else
            <a class="btn btn-login" href="/login">
                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
            </a>
            <a class="btn btn-register" href="/register">
                <i class="fas fa-user-plus"></i> Registrarse
            </a>
        @endauth
    </div>
</nav>
