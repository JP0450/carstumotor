@extends('layouts.admin')
@section('title', 'Solicitudes de compra')
@section('page_title', 'Solicitudes de asesoría de compra')
@section('page_subtitle', 'Solicitudes asignadas a tu bandeja de trabajo. Confirmar una asesoría no registra la venta del vehículo.')

@section('breadcrumbs')
    <a href="/">Inicio</a>
    <span>/</span>
    <span>Solicitudes de compra</span>
@endsection

@section('content')
<div class="toolbar">
    <h2><i class="fas fa-calendar-check" style="color:#ff6b35;"></i> Bandeja de solicitudes</h2>
    <a href="{{ route('finanzas.index') }}" class="btn-ghost"><i class="fas fa-coins"></i> Finanzas</a>
</div>

<div class="card-panel">
    @if ($solicitudes->isEmpty())
        <p style="padding:2rem;text-align:center;color:#71717a;">No hay solicitudes de asesoría asignadas.</p>
    @else
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha solicitada</th>
                        <th>Vehículo</th>
                        <th>Cliente y contacto</th>
                        <th>Asignada a</th>
                        <th>Comentarios</th>
                        <th>Asistencia del cliente</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($solicitudes as $solicitud)
                        <tr>
                            <td>{{ $solicitud->fecha_hora->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $solicitud->vehiculo?->marca ?? 'Vehículo eliminado' }}
                                {{ $solicitud->vehiculo?->modelo ?? '' }}
                            </td>
                            <td>
                                <strong>{{ $solicitud->nombre_contacto }}</strong><br>
                                {{ $solicitud->user?->email }}<br>
                                <a href="tel:{{ $solicitud->telefono_contacto }}">{{ $solicitud->telefono_contacto }}</a>
                            </td>
                            <td>{{ $solicitud->responsable?->name ?? 'Sin asignar' }}</td>
                            <td>{{ $solicitud->motivo }}</td>
                            <td>
                                @if ($solicitud->cliente_confirmo_at)
                                    Confirmó {{ $solicitud->cliente_confirmo_at->format('d/m/Y H:i') }}
                                @else
                                    Sin confirmar
                                @endif
                            </td>
                            <td>
                                <form method="post" action="{{ route('admin.compras.status', $solicitud) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="estado" aria-label="Estado de solicitud {{ $solicitud->id }}">
                                        @foreach (['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'cancelada' => 'Cancelada'] as $value => $label)
                                            <option value="{{ $value }}" @selected($solicitud->estado === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-action">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
