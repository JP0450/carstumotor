@extends('layouts.admin')

@section('title', 'Vehículos')
@section('page_title', 'Gestión de vehículos')
@section('page_subtitle', 'Alta, edición y baja del catálogo público. Solo roles autorizados pueden acceder a esta sección.')

@section('breadcrumbs')
    <a href="/">Inicio</a>
    <span>/</span>
    <a href="{{ route('catalogo') }}">Catálogo</a>
    <span>/</span>
    <span>Vehículos</span>
@endsection

@section('content')
    <div class="toolbar">
        <h2><i class="fas fa-car-side" style="color:#ff6b35;"></i> Listado</h2>
        <a href="{{ route('admin.vehiculos.create') }}" class="btn-action">
            <i class="fas fa-plus"></i> Nuevo vehículo
        </a>
    </div>

    <div class="card-panel">
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Marca / Modelo</th>
                        <th>Color</th>
                        <th>Puertas</th>
                        <th>HP</th>
                        <th>Precio cliente</th>
                        <th>Disponible</th>
                        <th style="min-width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehiculos as $v)
                        <tr>
                            <td>{{ $v->id }}</td>
                            <td><strong>{{ $v->marca }}</strong> {{ $v->modelo }}</td>
                            <td>{{ $v->color }}</td>
                            <td>{{ $v->puertas }}</td>
                            <td>{{ $v->hp }}</td>
                            <td>${{ number_format((float) $v->precio_cliente, 0, ',', '.') }}</td>
                            <td>
                                @if ($v->disponible)
                                    <span class="pill pill-yes"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="pill pill-no"><i class="fas fa-eye-slash"></i> No</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.vehiculos.edit', $v) }}" class="btn-ghost">
                                        <i class="fas fa-pen"></i> Editar
                                    </a>
                                    <form action="{{ route('admin.vehiculos.destroy', $v) }}" method="post" onsubmit="return confirm('¿Eliminar este vehículo del catálogo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-ghost btn-danger">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:2rem;color:#666;">
                                No hay vehículos registrados. Creá el primero con el botón <strong>Nuevo vehículo</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
