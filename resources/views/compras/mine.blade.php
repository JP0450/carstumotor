@extends('layouts.app')
@section('title', 'Mis solicitudes de compra - carsTUmotor')
@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Mi cuenta</p>
            <h1 class="mt-1 text-3xl font-black text-slate-900">Mis solicitudes de asesoría</h1>
            <p class="mt-2 text-zinc-600">Consulta el horario solicitado y el estado de cada solicitud enviada a Contabilidad.</p>
        </div>
        <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 rounded-xl border border-zinc-300 px-4 py-2.5 font-bold text-slate-800 hover:border-teal-500">
            <i class="fas fa-car"></i> Ver catálogo
        </a>
    </div>

    <div class="mt-6 grid gap-4">
        @forelse ($solicitudes as $solicitud)
            <article class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            {{ $solicitud->vehiculo?->marca ?? 'Vehículo' }} {{ $solicitud->vehiculo?->modelo ?? 'no disponible' }}
                        </h2>
                        <p class="mt-1 text-sm text-zinc-600">Horario preferido: {{ $solicitud->fecha_hora->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-teal-50 px-3 py-1 text-sm font-bold text-teal-800">{{ ucfirst($solicitud->estado) }}</span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">Contacto: {{ $solicitud->nombre_contacto }} · {{ $solicitud->telefono_contacto }}</p>
                <p class="mt-1 text-sm text-zinc-600">Responsable: {{ $solicitud->responsable?->name ?? 'Pendiente de asignación' }}</p>
                <p class="mt-3 text-xs text-zinc-500">La solicitud es una asesoría; no confirma una compra ni reserva el vehículo.</p>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white py-12 text-center text-zinc-500">
                Aún no has enviado solicitudes de asesoría.
            </div>
        @endforelse
    </div>
</section>
@endsection
