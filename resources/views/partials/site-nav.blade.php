@php
    $roleLabels = [
        \App\Models\User::ROLE_EXTERNO => 'Cliente',
        \App\Models\User::ROLE_JEFE => 'Jefe',
        \App\Models\User::ROLE_NEGOCIOS => 'Negocios internacionales',
        \App\Models\User::ROLE_CONTADOR => 'Contador',
    ];
@endphp
<nav class="sticky top-0 z-40 bg-slate-950 border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2 font-black text-white tracking-tight">
            <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-teal-400 to-amber-400 grid place-items-center text-slate-950"><i class="fas fa-car-side"></i></span>
            <span class="text-lg">cars<span class="text-teal-400">TU</span>motor</span>
        </a>
        <div class="flex items-center gap-2">
        @auth
            @php
                $u = auth()->user();
                $initial = mb_strtoupper(mb_substr($u->name ?: '?', 0, 1));
                $roleLabel = $u->role ? ($roleLabels[$u->role] ?? ucfirst(str_replace('_',' ',(string)$u->role))) : 'Usuario';
            @endphp
            <details class="user-menu relative">
                <summary class="list-none cursor-pointer inline-flex items-center gap-2 pl-1 pr-3 py-1 rounded-full bg-white/10 border border-white/15 text-white font-semibold hover:bg-white/15 transition">
                    <span class="w-8 h-8 rounded-full grid place-items-center font-black text-slate-900 bg-gradient-to-br from-teal-400 to-amber-400 text-sm">{{ $initial }}</span>
                    <span class="hidden sm:inline max-w-[140px] truncate">{{ $u->name }}</span>
                    <i class="fas fa-chevron-down text-white/70 text-xs"></i>
                </summary>
                <div class="user-menu-dropdown absolute right-0 mt-2 min-w-[280px] bg-white rounded-2xl border border-zinc-200 shadow-xl p-1.5 z-50">
                    <div class="px-3 pt-2 pb-3 border-b border-zinc-100 mb-1">
                        <div class="font-black text-slate-900">{{ $u->name }}</div>
                        <div class="text-sm text-zinc-500 break-all">{{ $u->email }}</div>
                        <div class="mt-1.5 flex flex-wrap gap-1.5 items-center text-xs text-zinc-500">
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-zinc-100 border border-zinc-200 font-bold text-slate-700"><i class="fas fa-id-badge text-teal-600"></i> {{ $roleLabel }}</span>
                            @if($u->phone)<span><i class="fas fa-phone text-teal-600"></i> {{ $u->phone }}</span>@endif
                        </div>
                    </div>
                    @unless($u->hasVerifiedEmail())
                        <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-50 border border-amber-200 font-bold text-amber-900" href="{{ route('verification.notice') }}"><i class="fas fa-envelope-circle-check"></i> Verificar correo</a>
                    @endunless
                    <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-bold text-slate-800" href="/catalogo"><i class="fas fa-layer-group text-teal-600 w-4 text-center"></i> Catálogo</a>
                    <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-bold text-slate-800" href="{{ route('profile.show') }}"><i class="fas fa-user-circle text-teal-600 w-4"></i> Mi perfil</a>
                    @php $isAdmin = in_array(strtolower(trim($u->role ?? '')), [\App\Models\User::ROLE_JEFE, \App\Models\User::ROLE_CONTADOR], true); @endphp
                    @if($isAdmin)
                        <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-bold text-slate-800" href="{{ route('admin.vehiculos.index') }}"><i class="fas fa-clipboard-list text-teal-600 w-4"></i> Gestionar vehículos</a>
                        <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-bold text-slate-800" href="{{ route('admin.dashboard.contador') }}"><i class="fas fa-chart-line text-teal-600 w-4"></i> Dashboard</a>
                    @endif
                    <a class="flex items-center gap-2 px-3 py-2.5 rounded-xl hover:bg-zinc-50 font-bold text-slate-800" href="/"><i class="fas fa-house text-teal-600 w-4"></i> Inicio</a>
                    <div class="pt-1.5 mt-1 border-t border-zinc-100">
                        <form method="POST" action="/logout">@csrf<button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border border-zinc-200 bg-white font-black text-slate-900 hover:border-teal-400"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</button></form>
                    </div>
                </div>
            </details>
        @else
            <a class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/20 text-white font-bold hover:bg-white/10 transition" href="/login"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black shadow hover:opacity-90 transition" href="/register"><i class="fas fa-user-plus"></i> Registrarse</a>
        @endauth
        </div>
    </div>
</nav>
