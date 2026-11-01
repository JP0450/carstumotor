@extends('layouts.app')
@section('title', $vehiculo->marca . ' ' . $vehiculo->modelo . ' - carsTUmotor')
@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 text-teal-700 font-bold hover:underline">
        <i class="fas fa-arrow-left"></i> Volver al catálogo
    </a>

    <article class="mt-6 overflow-hidden rounded-3xl bg-white border border-zinc-200 shadow-sm">
        <div class="aspect-[16/8] bg-gradient-to-br from-slate-900 via-[#16213e] to-teal-900 grid place-items-center overflow-hidden">
            @if($vehiculo->imagen)
                <img src="{{ $vehiculo->imagen }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}" class="w-full h-full object-cover">
            @else
                <i class="fas fa-car text-white/70 text-6xl"></i>
            @endif
        </div>

        <div class="p-6 sm:p-8">
            <p class="text-sm font-bold uppercase tracking-wide text-teal-700">{{ $vehiculo->marca }}</p>
            <h1 class="mt-1 text-3xl sm:text-4xl font-black text-slate-900">{{ $vehiculo->modelo }}</h1>
            <p class="mt-3 text-2xl font-black text-slate-800">${{ number_format($vehiculo->precio_cliente, 0, ',', '.') }}</p>

            <dl class="mt-8 grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-4">
                    <dt class="text-sm text-zinc-500">Color</dt>
                    <dd class="mt-1 font-bold text-slate-900">
                        {{ $vehiculo->color }}@if($vehiculo->color_secundario) / {{ $vehiculo->color_secundario }}@endif
                    </dd>
                </div>
                <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-4">
                    <dt class="text-sm text-zinc-500">Puertas</dt>
                    <dd class="mt-1 font-bold text-slate-900">{{ $vehiculo->puertas }}</dd>
                </div>
                <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-4">
                    <dt class="text-sm text-zinc-500">Potencia</dt>
                    <dd class="mt-1 font-bold text-slate-900">{{ $vehiculo->hp }} HP</dd>
                </div>
            </dl>
        </div>
    </article>
</section>
@endsection
