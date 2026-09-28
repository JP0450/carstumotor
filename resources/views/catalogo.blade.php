@extends('layouts.app')
@section('title', 'Catálogo - carsTUmotor')
@section('content')
<section class="bg-gradient-to-br from-slate-950 via-[#16213e] to-slate-900 text-white relative overflow-hidden">
    <div class="absolute -top-32 -right-32 w-[520px] h-[520px] rounded-full bg-teal-500/15 blur-3xl"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="text-sm text-white/70 flex items-center gap-2"><a href="/" class="hover:underline text-white/90">Inicio</a><span>/</span><span>Catálogo</span></div>
        <h1 class="mt-2 text-3xl sm:text-4xl font-black tracking-tight bg-gradient-to-r from-white to-amber-200 bg-clip-text text-transparent">Catálogo de vehículos</h1>
        <p class="mt-2 text-white/75 max-w-2xl">
            @auth Hola, {{ strtok(auth()->user()->name, ' ') }} — filtrá por marca, color o precio máximo. @else Explorá nuestros vehículos disponibles y agendá tu asesoría. @endauth
        </p>
        @auth @php $isAdmin = in_array(strtolower(trim(auth()->user()->role ?? '')), [\App\Models\User::ROLE_JEFE, \App\Models\User::ROLE_CONTADOR], true); @endphp
            @if($isAdmin)
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('admin.vehiculos.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/20 font-bold hover:bg-white/15"><i class="fas fa-clipboard-list"></i> CRUD vehículos</a>
                    <a href="{{ route('admin.dashboard.contador') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-chart-line"></i> Dashboard</a>
                </div>
            @endif
        @endauth
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative">
    <form method="GET" action="/catalogo" class="bg-white rounded-2xl shadow-lg border border-zinc-200 p-3 grid grid-cols-1 md:grid-cols-[1.7fr_1fr_1fr_auto] gap-2 items-center">
        <label class="flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5">
            <i class="fas fa-magnifying-glass text-teal-600"></i>
            <input type="text" name="marca" value="{{ request('marca') }}" placeholder="Buscar por marca o modelo" class="w-full bg-transparent outline-none text-sm">
        </label>
        <label class="flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5">
            <i class="fas fa-palette text-teal-600"></i>
            <select name="color" class="w-full bg-transparent outline-none text-sm">
                <option value="">Todos los colores</option>
                @foreach(['Rojo','Azul','Negro','Blanco','Gris','Plata'] as $c)<option value="{{ $c }}" @selected(request('color')==$c)>{{ $c }}</option>@endforeach
            </select>
        </label>
        <label class="flex items-center gap-2 bg-zinc-50 border border-zinc-200 rounded-xl px-3 py-2.5">
            <i class="fas fa-dollar-sign text-teal-600"></i>
            <input type="number" name="precio_max" value="{{ request('precio_max') }}" placeholder="Precio máximo" class="w-full bg-transparent outline-none text-sm">
        </label>
        <button type="submit" class="inline-flex justify-center items-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-bolt"></i> Ver resultados</button>
    </form>
</div>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
        <div>
            <h2 class="text-xl font-black text-slate-900">Vehículos destacados</h2>
            <p class="text-sm text-zinc-500">Mostrando vehículos disponibles.</p>
        </div>
        <p class="text-sm font-bold text-zinc-600">Resultados: {{ $vehiculos->count() }}</p>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($vehiculos as $vehiculo)
        <article class="bg-white rounded-2xl overflow-hidden border border-zinc-200 shadow-sm hover:shadow-lg hover:border-teal-200 hover:-translate-y-1 transition flex flex-col">
            <div class="relative aspect-[16/10] bg-gradient-to-br from-slate-900 via-[#16213e] to-teal-900 grid place-items-center overflow-hidden">
                <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/10 border border-white/20 text-white text-xs font-bold backdrop-blur">
                    <i class="fas {{ $vehiculo->disponible ? 'fa-check-circle' : 'fa-times-circle' }}"></i> {{ $vehiculo->disponible ? 'Disponible' : 'No disponible' }}
                </span>
                @if($vehiculo->imagen)
                    <img src="{{ $vehiculo->imagen }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}" class="w-full h-full object-cover">
                @else
                    <i class="fas fa-car text-white/70 text-4xl"></i>
                @endif
            </div>
            <div class="p-4 flex flex-col gap-2 flex-1">
                <div class="flex items-baseline justify-between gap-2">
                    <div class="font-black text-slate-900">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</div>
                    <div class="text-sm font-bold text-zinc-500">{{ $vehiculo->color }}</div>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <div class="bg-zinc-50 border border-zinc-200 rounded-xl px-2 py-2 text-center text-xs font-bold text-zinc-600"><i class="fas fa-door-open text-teal-600"></i> {{ $vehiculo->puertas }} pta</div>
                    <div class="bg-zinc-50 border border-zinc-200 rounded-xl px-2 py-2 text-center text-xs font-bold text-zinc-600"><i class="fas fa-gauge-high text-teal-600"></i> {{ $vehiculo->hp }} HP</div>
                    <div class="bg-zinc-50 border border-zinc-200 rounded-xl px-2 py-2 text-center text-xs font-bold text-zinc-700">${{ number_format($vehiculo->precio_cliente, 0, ',', '.') }}</div>
                </div>
                <a href="#" class="mt-auto inline-flex justify-center items-center gap-2 w-full py-2.5 rounded-xl bg-gradient-to-br from-teal-500 to-amber-400 text-slate-900 font-black"><i class="fas fa-eye"></i> Ver ficha</a>
            </div>
        </article>
        @empty
        <div class="col-span-full text-center py-12 text-zinc-500 bg-white rounded-2xl border border-dashed border-zinc-300">
            <i class="fas fa-car text-4xl text-zinc-300"></i>
            <p class="mt-2">No se encontraron vehículos con los filtros seleccionados.</p>
        </div>
        @endforelse
    </div>
</section>
@endsection
