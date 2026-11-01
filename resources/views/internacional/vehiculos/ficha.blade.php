@extends('layouts.internacional')

@php
    $mostrarDemo = $esEjemplo || !$info->vin || str_starts_with($info->vin, 'DEMO-');
    $moneda = $info->moneda ?: 'USD';
@endphp

@section('title', 'Ficha internacional — '.$vehiculo->marca.' '.$vehiculo->modelo)

@section('content')
    <style>
        .international-sheet { width:min(760px, 100%); margin:0 auto; padding:1.25rem 1rem 3rem; }
        .international-sheet * { box-sizing:border-box; }
        .sheet-topline { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin:.1rem 0 .65rem; }
        .sheet-topline h1 { margin:0; font-size:.95rem; font-weight:700; color:#f4f4f5; }
        .demo-tag { padding:.18rem .6rem; border-radius:999px; background:#063b22; color:#4ade80; font-size:.68rem; font-weight:800; }
        .sheet-photo { position:relative; overflow:hidden; width:100%; aspect-ratio:16/9; border-radius:1rem; background:linear-gradient(145deg,#27272a,#09090b 60%,#164e63); }
        .sheet-photo img { width:100%; height:100%; display:block; object-fit:cover; }
        .sheet-photo-placeholder { display:grid; width:100%; height:100%; place-items:center; color:#a1a1aa; font-size:4rem; }
        .photo-caption { position:absolute; bottom:.55rem; left:.55rem; padding:.2rem .45rem; border-radius:.4rem; background:#09090bcc; color:#d4d4d8; font-size:.62rem; }
        .vehicle-heading { padding:1rem 0 .7rem; border-bottom:1px solid #27272a; }
        .vehicle-heading h2 { margin:0; color:#fafafa; font-size:1.2rem; font-weight:800; }
        .vehicle-heading p { margin:.4rem 0 0; color:#a1a1aa; font-size:.78rem; }
        .vehicle-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem 1.5rem; padding:1rem .25rem; border-bottom:1px solid #27272a; }
        .summary-item span { display:block; margin-bottom:.16rem; color:#a1a1aa; font-size:.68rem; }
        .summary-item strong { display:block; color:#f4f4f5; font-size:.82rem; font-weight:700; overflow-wrap:anywhere; }
        .sheet-note { margin:1rem 0; padding:.65rem .75rem; border:1px solid #14532d; border-radius:.65rem; background:#052e16; color:#bbf7d0; font-size:.76rem; line-height:1.5; }
        .sheet-errors { margin:1rem 0; padding:.75rem 1rem; border:1px solid #7f1d1d; border-radius:.65rem; background:#450a0a; color:#fecaca; }
        .sheet-errors ul { margin:.35rem 0 0; padding-left:1.25rem; }
        .sheet-section { border-bottom:1px solid #27272a; }
        .sheet-section summary { display:flex; align-items:center; gap:.75rem; min-height:2.55rem; color:#f4f4f5; cursor:pointer; list-style:none; font-size:.78rem; font-weight:750; }
        .sheet-section summary::-webkit-details-marker { display:none; }
        .sheet-section summary::after { content:'+'; margin-left:auto; color:#a1a1aa; font-size:1rem; }
        .sheet-section[open] summary::after { content:'−'; }
        .section-number { display:grid; width:1.4rem; height:1.4rem; flex:0 0 1.4rem; place-items:center; border-radius:.45rem; background:#27272a; color:#d4d4d8; font-size:.65rem; }
        .section-content { padding:.15rem 0 1rem; }
        .sheet-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.75rem; }
        .sheet-field { display:flex; flex-direction:column; gap:.32rem; min-width:0; }
        .sheet-field.full { grid-column:1/-1; }
        .sheet-field label { color:#a1a1aa; font-size:.68rem; }
        .sheet-field input, .sheet-field select { width:100%; min-height:2.35rem; padding:.55rem .65rem; border:1px solid #3f3f46; border-radius:.5rem; outline:none; background:#111113; color:#f4f4f5; font:inherit; font-size:.78rem; }
        .sheet-field input:focus, .sheet-field select:focus { border-color:#14b8a6; box-shadow:0 0 0 2px #0f766e55; }
        .sheet-field input::placeholder { color:#71717a; }
        .sheet-help { margin:0 0 .8rem; color:#a1a1aa; font-size:.72rem; }
        .sheet-actions { position:sticky; bottom:.5rem; display:flex; justify-content:flex-end; padding:.75rem 0; background:linear-gradient(transparent,#050505 20%); z-index:2; }
        .sheet-button { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; min-height:2.6rem; padding:.65rem 1rem; border:1px solid #0f766e; border-radius:.6rem; background:#0f766e; color:white; cursor:pointer; font:inherit; font-size:.78rem; font-weight:750; text-decoration:none; }
        .sheet-button:hover { background:#115e59; }
        .sheet-button.secondary { border-color:#3f3f46; background:#18181b; }
        .sheet-button.danger { border-color:#7f1d1d; background:#450a0a; }
        .document-list { display:grid; gap:.1rem; }
        .document-row { display:flex; align-items:center; gap:.7rem; padding:.65rem 0; border-bottom:1px solid #27272a; }
        .document-icon { color:#f87171; font-size:1rem; }
        .document-name { min-width:0; flex:1; }
        .document-name strong { display:block; overflow:hidden; color:#f4f4f5; font-size:.76rem; text-overflow:ellipsis; white-space:nowrap; }
        .document-name span { display:block; margin-top:.15rem; color:#a1a1aa; font-size:.66rem; }
        .document-tools { display:flex; align-items:center; gap:.65rem; }
        .document-tools a, .document-tools button { padding:0; border:0; background:transparent; color:#d4d4d8; cursor:pointer; font-size:.8rem; text-decoration:none; }
        .document-tools a:hover, .document-tools button:hover { color:#5eead4; }
        .document-tools form { display:inline; }
        .upload-box { display:grid; gap:.7rem; margin-top:.8rem; padding:1rem; border:1px solid #3f3f46; border-radius:.65rem; background:#09090b; }
        .empty-documents { padding:.75rem 0; color:#a1a1aa; font-size:.74rem; }
        .sheet-footnote { margin:.85rem 0 0; color:#71717a; font-size:.68rem; }
        @media (max-width:520px) {
            .international-sheet { padding:1rem .8rem 2rem; }
            .vehicle-summary { gap:.7rem 1rem; }
            .sheet-fields { grid-template-columns:1fr; }
            .sheet-field.full { grid-column:auto; }
            .document-row { gap:.5rem; }
            .document-tools { gap:.55rem; }
        }
    </style>

    <div class="international-sheet">
        <div class="sheet-topline">
            <h1>Ficha del vehículo</h1>
            @if ($mostrarDemo)
                <span class="demo-tag">Ejemplo</span>
            @endif
        </div>

        <div class="sheet-photo">
            @if ($vehiculo->imagen)
                <img src="{{ $vehiculo->imagen }}" alt="{{ $vehiculo->marca }} {{ $vehiculo->modelo }}">
            @else
                <div class="sheet-photo-placeholder" aria-label="Vehículo sin imagen">
                    <i class="fas fa-car-side"></i>
                </div>
            @endif
            <span class="photo-caption">{{ $vehiculo->marca }} {{ $vehiculo->modelo }}</span>
        </div>

        <header class="vehicle-heading">
            <h2>{{ $vehiculo->marca }} {{ $vehiculo->modelo }}@if($info->version) {{ $info->version }}@endif</h2>
            <p>
                VIN {{ $info->vin ?: 'Pendiente' }}
                @if ($info->anio) · Año {{ $info->anio }} @endif
            </p>
        </header>

        <div class="vehicle-summary">
            <div class="summary-item">
                <span>País de origen</span>
                <strong>{{ $info->pais_origen ?: 'Pendiente' }}</strong>
            </div>
            <div class="summary-item">
                <span>Destino</span>
                <strong>{{ $info->pais_destino ?: 'Pendiente' }}</strong>
            </div>
            <div class="summary-item">
                <span>Valor CIF</span>
                <strong>{{ $moneda }} {{ number_format((float) ($info->valor_cif ?? 0), 0, ',', '.') }}</strong>
            </div>
            <div class="summary-item">
                <span>Estado</span>
                <strong>{{ $info->estado ?: 'Pendiente' }}</strong>
            </div>
        </div>

        @if ($errors->any())
            <div class="sheet-errors" role="alert">
                <strong>Revisa estos campos:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($mostrarDemo)
            <div class="sheet-note">
                <strong>Datos ficticios de demostración.</strong> Edita la ficha y pulsa guardar para conservarlos.
            </div>
        @endif

        <form method="post" action="{{ route('internacional.vehiculos.update', $vehiculo) }}">
            @csrf
            @method('PUT')

            <details class="sheet-section" @if ($errors->hasAny(['anio', 'version', 'vin', 'numero_motor', 'tipo_vehiculo', 'kilometraje', 'combustible', 'estado'])) open @endif>
                <summary><span class="section-number">01</span> Información del vehículo</summary>
                <div class="section-content sheet-fields">
                    <div class="sheet-field"><label>Marca</label><input value="{{ $vehiculo->marca }}" readonly></div>
                    <div class="sheet-field"><label>Modelo</label><input value="{{ $vehiculo->modelo }}" readonly></div>
                    <div class="sheet-field"><label>Color principal</label><input value="{{ $vehiculo->color }}" readonly></div>
                    <div class="sheet-field"><label>Color secundario</label><input value="{{ $vehiculo->color_secundario ?: '—' }}" readonly></div>
                    <div class="sheet-field"><label for="anio">Año</label><input id="anio" name="anio" type="number" min="1886" max="2100" value="{{ old('anio', $info->anio) }}"></div>
                    <div class="sheet-field"><label for="version">Versión</label><input id="version" name="version" value="{{ old('version', $info->version) }}"></div>
                    <div class="sheet-field"><label for="vin">VIN</label><input id="vin" name="vin" maxlength="30" value="{{ old('vin', $info->vin) }}"></div>
                    <div class="sheet-field"><label for="numero_motor">Número de motor</label><input id="numero_motor" name="numero_motor" value="{{ old('numero_motor', $info->numero_motor) }}"></div>
                    <div class="sheet-field"><label for="tipo_vehiculo">Tipo de vehículo</label><input id="tipo_vehiculo" name="tipo_vehiculo" value="{{ old('tipo_vehiculo', $info->tipo_vehiculo) }}"></div>
                    <div class="sheet-field"><label for="kilometraje">Kilometraje</label><input id="kilometraje" name="kilometraje" type="number" min="0" value="{{ old('kilometraje', $info->kilometraje) }}"></div>
                    <div class="sheet-field"><label for="combustible">Combustible</label><input id="combustible" name="combustible" value="{{ old('combustible', $info->combustible) }}"></div>
                    <div class="sheet-field"><label for="estado">Estado</label><input id="estado" name="estado" value="{{ old('estado', $info->estado) }}"></div>
                </div>
            </details>

            <details class="sheet-section" @if ($errors->hasAny(['proveedor_internacional', 'pais_compra', 'precio_adquisicion', 'moneda', 'fecha_compra', 'incoterm'])) open @endif>
                <summary><span class="section-number">02</span> Información comercial</summary>
                <div class="section-content sheet-fields">
                    <div class="sheet-field full"><label for="proveedor_internacional">Proveedor internacional</label><input id="proveedor_internacional" name="proveedor_internacional" value="{{ old('proveedor_internacional', $info->proveedor_internacional) }}"></div>
                    <div class="sheet-field"><label for="pais_compra">País de compra</label><input id="pais_compra" name="pais_compra" value="{{ old('pais_compra', $info->pais_compra) }}"></div>
                    <div class="sheet-field"><label for="precio_adquisicion">Precio de adquisición</label><input id="precio_adquisicion" name="precio_adquisicion" type="number" min="0" step="0.01" value="{{ old('precio_adquisicion', $info->precio_adquisicion) }}"></div>
                    <div class="sheet-field"><label for="moneda">Moneda</label><input id="moneda" name="moneda" maxlength="10" value="{{ old('moneda', $info->moneda) }}"></div>
                    <div class="sheet-field"><label for="fecha_compra">Fecha de compra</label><input id="fecha_compra" name="fecha_compra" type="date" value="{{ old('fecha_compra', $info->fecha_compra?->format('Y-m-d')) }}"></div>
                    <div class="sheet-field"><label for="incoterm">Incoterm</label><input id="incoterm" name="incoterm" maxlength="20" value="{{ old('incoterm', $info->incoterm) }}"></div>
                </div>
            </details>

            <details class="sheet-section" @if ($errors->hasAny(['pais_origen', 'pais_procedencia', 'pais_destino', 'puerto_salida', 'puerto_llegada', 'transportadora', 'agencia_aduanas', 'fecha_embarque', 'fecha_llegada', 'numero_declaracion_importacion'])) open @endif>
                <summary><span class="section-number">03</span> Importación y logística</summary>
                <div class="section-content sheet-fields">
                    <div class="sheet-field"><label for="pais_origen">País de origen</label><input id="pais_origen" name="pais_origen" value="{{ old('pais_origen', $info->pais_origen) }}"></div>
                    <div class="sheet-field"><label for="pais_procedencia">País de procedencia</label><input id="pais_procedencia" name="pais_procedencia" value="{{ old('pais_procedencia', $info->pais_procedencia) }}"></div>
                    <div class="sheet-field"><label for="pais_destino">País de destino</label><input id="pais_destino" name="pais_destino" value="{{ old('pais_destino', $info->pais_destino) }}"></div>
                    <div class="sheet-field"><label for="puerto_salida">Puerto de salida</label><input id="puerto_salida" name="puerto_salida" value="{{ old('puerto_salida', $info->puerto_salida) }}"></div>
                    <div class="sheet-field"><label for="puerto_llegada">Puerto de llegada</label><input id="puerto_llegada" name="puerto_llegada" value="{{ old('puerto_llegada', $info->puerto_llegada) }}"></div>
                    <div class="sheet-field"><label for="transportadora">Transportadora</label><input id="transportadora" name="transportadora" value="{{ old('transportadora', $info->transportadora) }}"></div>
                    <div class="sheet-field"><label for="agencia_aduanas">Agencia de aduanas</label><input id="agencia_aduanas" name="agencia_aduanas" value="{{ old('agencia_aduanas', $info->agencia_aduanas) }}"></div>
                    <div class="sheet-field"><label for="fecha_embarque">Fecha de embarque</label><input id="fecha_embarque" name="fecha_embarque" type="date" value="{{ old('fecha_embarque', $info->fecha_embarque?->format('Y-m-d')) }}"></div>
                    <div class="sheet-field"><label for="fecha_llegada">Fecha de llegada</label><input id="fecha_llegada" name="fecha_llegada" type="date" value="{{ old('fecha_llegada', $info->fecha_llegada?->format('Y-m-d')) }}"></div>
                    <div class="sheet-field full"><label for="numero_declaracion_importacion">Número de declaración de importación</label><input id="numero_declaracion_importacion" name="numero_declaracion_importacion" value="{{ old('numero_declaracion_importacion', $info->numero_declaracion_importacion) }}"></div>
                </div>
            </details>

            <details class="sheet-section" @if ($errors->hasAny(['valor_fob', 'flete_internacional', 'seguro', 'valor_cif', 'aranceles', 'impuestos', 'gastos_aduaneros', 'costo_total_importacion'])) open @endif>
                <summary><span class="section-number">04</span> Costos y valores</summary>
                <div class="section-content">
                    <p class="sheet-help">Valores expresados en {{ $moneda }}.</p>
                    <div class="sheet-fields">
                        <div class="sheet-field"><label for="valor_fob">Valor FOB</label><input id="valor_fob" name="valor_fob" type="number" min="0" step="0.01" value="{{ old('valor_fob', $info->valor_fob) }}"></div>
                        <div class="sheet-field"><label for="flete_internacional">Flete internacional</label><input id="flete_internacional" name="flete_internacional" type="number" min="0" step="0.01" value="{{ old('flete_internacional', $info->flete_internacional) }}"></div>
                        <div class="sheet-field"><label for="seguro">Seguro</label><input id="seguro" name="seguro" type="number" min="0" step="0.01" value="{{ old('seguro', $info->seguro) }}"></div>
                        <div class="sheet-field"><label for="valor_cif">Valor CIF</label><input id="valor_cif" name="valor_cif" type="number" min="0" step="0.01" value="{{ old('valor_cif', $info->valor_cif) }}"></div>
                        <div class="sheet-field"><label for="aranceles">Aranceles</label><input id="aranceles" name="aranceles" type="number" min="0" step="0.01" value="{{ old('aranceles', $info->aranceles) }}"></div>
                        <div class="sheet-field"><label for="impuestos">Impuestos</label><input id="impuestos" name="impuestos" type="number" min="0" step="0.01" value="{{ old('impuestos', $info->impuestos) }}"></div>
                        <div class="sheet-field"><label for="gastos_aduaneros">Gastos aduaneros</label><input id="gastos_aduaneros" name="gastos_aduaneros" type="number" min="0" step="0.01" value="{{ old('gastos_aduaneros', $info->gastos_aduaneros) }}"></div>
                        <div class="sheet-field"><label for="costo_total_importacion">Costo total de importación</label><input id="costo_total_importacion" name="costo_total_importacion" type="number" min="0" step="0.01" value="{{ old('costo_total_importacion', $info->costo_total_importacion) }}"></div>
                    </div>
                </div>
            </details>

            <div class="sheet-actions">
                <button type="submit" class="sheet-button"><i class="fas fa-save"></i> Guardar ficha</button>
            </div>
        </form>

        <details class="sheet-section" open>
            <summary><span class="section-number">05</span> Documentos adjuntos ({{ $vehiculo->documentos->count() }})</summary>
            <div class="section-content">
                <div class="document-list">
                    @forelse ($vehiculo->documentos as $documento)
                        <div class="document-row">
                            <i class="fas fa-file-pdf document-icon" aria-hidden="true"></i>
                            <div class="document-name">
                                <strong>{{ $documento->nombre_archivo }}</strong>
                                <span>{{ $documento->tipo }} · PDF</span>
                            </div>
                            <div class="document-tools">
                                <a href="{{ route('internacional.vehiculos.documentos.show', [$vehiculo, $documento]) }}" target="_blank" rel="noopener" aria-label="Visualizar {{ $documento->nombre_archivo }}" title="Visualizar"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('internacional.vehiculos.documentos.download', [$vehiculo, $documento]) }}" aria-label="Descargar {{ $documento->nombre_archivo }}" title="Descargar"><i class="fas fa-download"></i></a>
                                <form method="post" action="{{ route('internacional.vehiculos.documentos.destroy', [$vehiculo, $documento]) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Eliminar {{ $documento->nombre_archivo }}" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="empty-documents">Aún no hay documentos adjuntos.</p>
                    @endforelse
                </div>

                <form method="post" action="{{ route('internacional.vehiculos.documentos.store', $vehiculo) }}" enctype="multipart/form-data" class="upload-box">
                    @csrf
                    <div class="sheet-field">
                        <label for="tipo">Categoría del documento</label>
                        <select id="tipo" name="tipo" required>
                            <option value="">Selecciona el tipo de documento</option>
                            @foreach ($tiposDocumento as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sheet-field">
                        <label for="archivo">Adjuntar documento PDF (máximo 10 MB)</label>
                        <input id="archivo" name="archivo" type="file" accept="application/pdf,.pdf" required>
                    </div>
                    <button type="submit" class="sheet-button secondary"><i class="fas fa-cloud-arrow-up"></i> Adjuntar documento PDF</button>
                </form>
                <p class="sheet-footnote">Prototipo ilustrativo. Utiliza únicamente datos y documentos ficticios.</p>
            </div>
        </details>
    </div>
@endsection
