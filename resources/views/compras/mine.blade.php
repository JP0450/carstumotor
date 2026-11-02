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
        <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 font-black text-white hover:bg-teal-800">
            <i class="fas fa-calendar-check"></i> Hacer una solicitud
        </a>
    </div>

    <div class="mt-6 grid gap-4">
        @if ($errors->any())
            <div class="rounded-xl border border-red-300 border-l-4 border-l-red-700 bg-red-50 p-4 text-sm text-red-950 shadow-sm" role="alert" aria-live="assertive">
                <p class="font-bold">No se pudo actualizar la cita:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

                @if ($solicitud->estado === 'cancelada')
                    <p class="mt-4 rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-700">Esta cita fue cancelada. No es posible editarla ni confirmar asistencia.</p>
                @else
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900" role="note">
                        Puedes editar el horario hasta el día anterior. El mismo día de la asesoría ya no podrás cambiar la fecha ni la hora.
                    </div>

                    @if ($solicitud->fecha_hora->isToday())
                        <p class="mt-3 text-sm font-bold text-red-700">Esta asesoría es hoy; ya no puedes modificar su horario.</p>
                    @elseif ($solicitud->fecha_hora->isPast())
                        <p class="mt-3 text-sm text-zinc-600">La fecha de esta asesoría ya pasó.</p>
                    @else
                        <form method="post" action="{{ route('compras.schedule.update', $solicitud) }}" class="mt-4 flex flex-wrap items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="fecha_hora_{{ $solicitud->id }}" class="block text-sm font-bold text-slate-800">Cambiar fecha y hora</label>
                                <input id="fecha_hora_{{ $solicitud->id }}" name="fecha_hora" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}"
                                    value="{{ old('fecha_hora', $solicitud->fecha_hora->format('Y-m-d\TH:i')) }}"
                                    aria-invalid="@error('fecha_hora') true @else false @enderror"
                                    class="mt-1 rounded-xl border @error('fecha_hora') border-red-500 @else border-zinc-300 @enderror px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                            </div>
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-teal-700 px-4 py-2.5 font-bold text-teal-800 hover:bg-teal-50">
                                <i class="fas fa-pen"></i> Guardar horario
                            </button>
                        </form>
                        @error('fecha_hora')
                            <p class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-900" role="alert">{{ $message }}</p>
                        @enderror
                    @endif

                    <div class="mt-4">
                        @if ($solicitud->cliente_confirmo_at)
                            <p class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-bold text-emerald-900">
                                <i class="fas fa-circle-check"></i> Confirmaste tu asistencia el {{ $solicitud->cliente_confirmo_at->format('d/m/Y H:i') }}.
                            </p>
                        @elseif (! $solicitud->fecha_hora->isPast())
                            <form method="post" action="{{ route('compras.attendance.confirm', $solicitud) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 font-black text-white hover:bg-teal-800">
                                    <i class="fas fa-circle-check"></i> Confirmar mi asistencia
                                </button>
                            </form>
                        @endif
                        <p class="mt-2 text-xs text-zinc-500">Tu confirmación registra que asistirás; no reemplaza la confirmación del horario por Contabilidad.</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-zinc-300 bg-white py-12 text-center text-zinc-500">
                Aún no has enviado solicitudes de asesoría.
            </div>
        @endforelse
    </div>
</section>
@endsection
