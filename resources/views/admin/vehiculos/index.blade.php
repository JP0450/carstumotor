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

    @if ($errors->any())
        <div class="errors" role="alert" style="margin-bottom:1rem;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

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
                        <th>Estado</th>
                        <th>Registrar venta</th>
                        <th style="min-width: 160px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehiculos as $v)
                        <tr>
                            <td>{{ $v->id }}</td>
                            <td><strong>{{ $v->marca }}</strong> {{ $v->modelo }}</td>
                            <td>{{ $v->color }}{{ $v->color_secundario ? ' / '.$v->color_secundario : '' }}</td>
                            <td>{{ $v->puertas }}</td>
                            <td>{{ $v->hp }}</td>
                            <td>${{ number_format((float) $v->precio_cliente, 0, ',', '.') }}</td>
                            <td>
                                @if ($v->ventaRegistrada)
                                    <span class="pill pill-no"><i class="fas fa-check"></i> Vendido</span>
                                @elseif ($v->disponible)
                                    <span class="pill pill-yes"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="pill pill-no"><i class="fas fa-eye-slash"></i> No</span>
                                @endif
                            </td>
                            <td>
                                @if ($v->ventaRegistrada)
                                    <span>COP {{ number_format((float) $v->ventaRegistrada->monto, 2, ',', '.') }}</span>
                                    <small style="display:block;color:#64748b;">{{ $v->ventaRegistrada->fecha->format('d/m/Y') }}</small>
                                @else
                                    <form action="{{ route('admin.vehiculos.venta', $v) }}" method="post" style="display:grid;gap:0.4rem;min-width:150px;" onsubmit="return confirm('¿Confirmar la venta y registrar este ingreso financiero?');">
                                        @csrf
                                        <label style="font-size:0.8rem;">Precio de venta (COP)
                                            <input name="monto" type="number" min="0.01" step="0.01" value="{{ $v->precio_cliente }}" required style="width:100%;padding:0.35rem;border:1px solid #cbd5e1;border-radius:6px;">
                                        </label>
                                        <label style="font-size:0.8rem;">Fecha
                                            <input name="fecha" type="date" value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required style="width:100%;padding:0.35rem;border:1px solid #cbd5e1;border-radius:6px;">
                                        </label>
                                        <small style="color:#64748b;">La venta descuenta una unidad del inventario asociado.</small>
                                        <button type="submit" class="btn-action" style="justify-content:center;">Registrar venta</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('internacional.vehiculos.edit', $v) }}" class="btn-ghost">
                                        <i class="fas fa-globe"></i> Ficha internacional
                                    </a>
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
                            <td colspan="9" style="text-align:center;padding:2rem;color:#666;">
                                No hay vehículos registrados. Creá el primero con el botón <strong>Nuevo vehículo</strong>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
