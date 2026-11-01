@extends('layouts.admin')

@section('title', 'Comercio internacional — Vehículos')
@section('page_title', 'Fichas internacionales de vehículos')
@section('page_subtitle', 'Consulta, completa y organiza la información de compra e importación por automóvil.')

@section('breadcrumbs')
    <a href="/">Inicio</a>
    <span>/</span>
    <a href="{{ route('catalogo') }}">Catálogo</a>
    <span>/</span>
    <span>Comercio internacional</span>
@endsection

@section('content')
    <div class="toolbar">
        <h2><i class="fas fa-globe" style="color:#0f766e;"></i> Vehículos registrados</h2>
        <a href="{{ route('admin.dashboard.gerencial') }}" class="btn-ghost">
            <i class="fas fa-chart-line"></i> Dashboard gerencial
        </a>
    </div>

    <div class="card-panel">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Vehículo</th>
                        <th>Color</th>
                        <th>Año</th>
                        <th>Estado de ficha</th>
                        <th>Documentos</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehiculos as $vehiculo)
                        <tr>
                            <td><strong>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</strong></td>
                            <td>{{ $vehiculo->color }}{{ $vehiculo->color_secundario ? ' / '.$vehiculo->color_secundario : '' }}</td>
                            <td>{{ $vehiculo->informacionInternacional?->anio ?? '—' }}</td>
                            <td>
                                @if ($vehiculo->informacionInternacional)
                                    <span class="pill pill-yes"><i class="fas fa-check"></i> Información guardada</span>
                                @else
                                    <span class="pill pill-no"><i class="fas fa-circle-info"></i> Pendiente</span>
                                @endif
                            </td>
                            <td>{{ $vehiculo->documentos->count() }}</td>
                            <td>
                                <a href="{{ route('internacional.vehiculos.edit', $vehiculo) }}" class="btn-ghost">
                                    <i class="fas fa-folder-open"></i> Abrir ficha
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:#666;">
                                No hay vehículos registrados. Primero agrega vehículos al catálogo para crear sus fichas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
