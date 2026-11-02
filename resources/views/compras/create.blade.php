@extends('layouts.app')
@section('title', 'Agendar asesoría - carsTUmotor')
@section('content')
<section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('catalogo.show', $vehiculo->id) }}" class="inline-flex items-center gap-2 text-teal-700 font-bold hover:underline">
        <i class="fas fa-arrow-left"></i> Volver a la ficha
    </a>

    <div class="mt-5 rounded-3xl bg-white border border-zinc-200 shadow-sm p-6 sm:p-8">
        <p class="text-sm font-bold uppercase tracking-wide text-teal-700">Solicitud para</p>
        <h1 class="mt-1 text-2xl sm:text-3xl font-black text-slate-900">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</h1>
        <p class="mt-2 text-lg font-bold text-slate-700">${{ number_format($vehiculo->precio_cliente, 0, ',', '.') }} COP</p>
        <p class="mt-3 text-sm text-zinc-600">Completa tus datos y el horario que prefieres. Contabilidad recibirá la solicitud y se pondrá en contacto contigo para confirmar la asesoría.</p>

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 border-l-4 border-l-red-700 bg-red-50 p-4 text-sm text-red-950 shadow-sm" role="alert" aria-live="assertive">
                <p class="font-bold">No se pudo enviar la solicitud:</p>
                <ul class="mt-1 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('catalogo.compra.store', $vehiculo) }}" class="mt-6 grid gap-5">
            @csrf
            <div>
                <label for="nombre_contacto" class="block text-sm font-bold text-slate-800">Nombre de contacto</label>
                <input id="nombre_contacto" name="nombre_contacto" type="text" required maxlength="255"
                    value="{{ old('nombre_contacto', $user->name) }}"
                    class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="telefono_contacto" class="block text-sm font-bold text-slate-800">Teléfono</label>
                <input id="telefono_contacto" name="telefono_contacto" type="tel" required maxlength="30" autocomplete="tel"
                    value="{{ old('telefono_contacto', $user->phone) }}"
                    placeholder="Ej. +57 300 123 4567"
                    class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="fecha_hora" class="block text-sm font-bold text-slate-800">Fecha y hora preferidas</label>
                <input id="fecha_hora" name="fecha_hora" type="datetime-local" required min="{{ now()->format('Y-m-d\TH:i') }}"
                    value="{{ old('fecha_hora') }}"
                    aria-invalid="@error('fecha_hora') true @else false @enderror"
                    class="mt-1 w-full rounded-xl border @error('fecha_hora') border-red-500 @else border-zinc-300 @enderror px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                @error('fecha_hora')
                    <p class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-900" role="alert">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-zinc-500">El horario queda sujeto a confirmación por parte de Contabilidad. No se puede seleccionar un horario ya elegido para este vehículo.</p>
            </div>
            <div>
                <label for="motivo" class="block text-sm font-bold text-slate-800">Comentarios adicionales (opcional)</label>
                <textarea id="motivo" name="motivo" rows="3" maxlength="500"
                    class="mt-1 w-full rounded-xl border border-zinc-300 px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">{{ old('motivo') }}</textarea>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                Enviar esta solicitud no reserva ni marca como vendido el vehículo. La disponibilidad se verifica nuevamente al enviarla.
            </div>
            <button type="submit" class="inline-flex justify-center items-center gap-2 rounded-xl bg-teal-700 px-5 py-3 font-black text-white hover:bg-teal-800">
                <i class="fas fa-calendar-check"></i> Enviar solicitud a Contabilidad
            </button>
        </form>
    </div>
</section>
@endsection
