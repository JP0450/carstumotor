@extends('layouts.admin')

@section('title', 'Nuevo vehículo')
@section('page_title', 'Nuevo vehículo')
@section('page_subtitle', 'Los datos se reflejan en el catálogo que ven los clientes cuando el vehículo está marcado como disponible.')

@section('breadcrumbs')
    <a href="/">Inicio</a>
    <span>/</span>
    <a href="{{ route('admin.vehiculos.index') }}">Vehículos</a>
    <span>/</span>
    <span>Nuevo</span>
@endsection

@section('content')
    <div class="toolbar">
        <h2><i class="fas fa-plus-circle" style="color:#ff6b35;"></i> Formulario</h2>
        <a href="{{ route('admin.vehiculos.index') }}" class="btn-ghost">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    <div class="card-panel">
        <div class="form-card">
            @if ($errors->any())
                <div class="errors">
                    <strong>Revisá los siguientes campos:</strong>
                    <ul>
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" action="{{ route('admin.vehiculos.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="marca">Marca</label>
                        <input id="marca" name="marca" type="text" value="{{ old('marca') }}" required maxlength="50">
                    </div>
                    <div class="field">
                        <label for="modelo">Modelo</label>
                        <input id="modelo" name="modelo" type="text" value="{{ old('modelo') }}" required maxlength="50">
                    </div>
                    <div class="field">
                        <label for="color">Color</label>
                        <input id="color" name="color" type="text" value="{{ old('color') }}" required maxlength="30">
                    </div>
                    <div class="field">
                        <label for="puertas">Puertas</label>
                        <select id="puertas" name="puertas" required>
                            @foreach (range(1, 5) as $n)
                                <option value="{{ $n }}" @selected((int) old('puertas', 4) === $n)>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="hp">Caballos de fuerza (HP)</label>
                        <input id="hp" name="hp" type="number" min="1" value="{{ old('hp') }}" required>
                    </div>
                    <div class="field">
                        <label for="precio_cliente">Precio al cliente</label>
                        <input id="precio_cliente" name="precio_cliente" type="number" step="0.01" min="0" value="{{ old('precio_cliente') }}" required>
                    </div>
                    <div class="field">
                        <label for="costo_empresa">Costo empresa</label>
                        <input id="costo_empresa" name="costo_empresa" type="number" step="0.01" min="0" value="{{ old('costo_empresa') }}" required>
                    </div>
                    <div class="field full">
                        <label for="imagen">URL de imagen (opcional)</label>
                        <input id="imagen" name="imagen" type="text" value="{{ old('imagen') }}" maxlength="255" placeholder="https://… o ruta relativa">
                        <p class="hint">Podés dejarlo vacío; en el catálogo se mostrará un placeholder.</p>
                    </div>
                    <div class="field full">
                        <div class="check-row">
                            <input id="disponible" name="disponible" type="checkbox" value="1" @checked(old('disponible', true))>
                            <label for="disponible" style="margin:0;font-weight:700;">Visible y disponible en catálogo público</label>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-action">
                        <i class="fas fa-save"></i> Guardar vehículo
                    </button>
                    <a href="{{ route('admin.vehiculos.index') }}" class="btn-ghost">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
