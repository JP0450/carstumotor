@extends('layouts.admin')

@section('title', 'Finanzas y contabilidad')
@section('body_class', 'finance-dark-layout')

@section('content')
    @php
        $conteoTotal = array_sum($conteos);
        $conteoIngresos = (int) ($conteos['ingreso'] ?? 0);
        $conteoEgresos = (int) ($conteos['gasto'] ?? 0)
            + (int) ($conteos['impuesto'] ?? 0)
            + (int) ($conteos['costo_venta'] ?? 0);
        $conteoCobrar = (int) ($conteos['cuenta_por_cobrar'] ?? 0);
        $conteoPagar = (int) ($conteos['cuenta_por_pagar'] ?? 0);
        $hayMovimientosMensuales = array_sum($serieMensual['ingresos']) + array_sum($serieMensual['egresos']) > 0;
        $formularioAbierto = $transaccion !== null || $errors->any();
    @endphp

    <style>
        .finance-dark-layout { background:#050505!important; color:#e5e7eb; }
        .finance-dark-layout .page-header { display:none; }
        .finance-dark-layout .admin-main { width:100%; max-width:none; margin:0; padding:1.25rem clamp(1rem, 3vw, 2.5rem) 3rem; }
        .finance-dark-layout footer { border-top:1px solid #202020; background:#050505; color:#737373; }
        .finance-page { --ink:#f4f4f5; --muted:#a3a3a3; --line:#292929; max-width:1120px; margin:0 auto; }
        .finance-heading { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.2rem; }
        .finance-heading h1 { margin:0; color:#f5f5f5; font-size:1.35rem; line-height:1.3; font-weight:750; }
        .finance-heading p { margin:.25rem 0 0; color:#a3a3a3; font-size:.86rem; }
        .finance-badge { display:inline-flex; align-items:center; gap:.4rem; border-radius:99px; padding:.28rem .7rem; background:#052e16; color:#4ade80; font-size:.74rem; font-weight:750; }
        .finance-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.65rem; margin:1.3rem 0; }
        .finance-stat { min-width:0; padding:.85rem 1rem; border:1px solid #222; border-radius:10px; background:#0b0b0b; }
        .finance-stat span { display:block; color:#a3a3a3; font-size:.76rem; }
        .finance-stat strong { display:block; margin:.25rem 0; color:#e5e5e5; font-size:1.35rem; font-weight:750; }
        .finance-stat strong.is-positive { color:#4ade80; }
        .finance-stat small { color:#737373; font-size:.68rem; }
        .finance-notice { margin:1rem 0 1.25rem; padding:.7rem .85rem; border-left:2px solid #16a34a; background:#0b0b0b; color:#a3a3a3; font-size:.75rem; line-height:1.5; }
        .finance-section { padding:1.1rem 0; border-top:1px solid #292929; }
        .finance-section h2, .finance-section summary { margin:0; color:#f4f4f5; font-size:1rem; font-weight:750; }
        .finance-section-heading { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:.7rem; margin-bottom:.75rem; }
        .finance-section-heading p { margin:.25rem 0 0; color:#8a8a8a; font-size:.72rem; }
        .finance-chart { height:260px; position:relative; }
        .finance-empty { display:grid; min-height:130px; place-content:center; padding:1rem; border:1px dashed #333; border-radius:9px; color:#a3a3a3; text-align:center; font-size:.8rem; line-height:1.5; }
        .finance-empty strong { display:block; margin-bottom:.3rem; color:#e5e5e5; }
        .finance-accounts { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.7rem; }
        .finance-account { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.9rem; border:1px solid #252525; border-radius:9px; background:#0a0a0a; }
        .finance-account span { color:#b3b3b3; font-size:.82rem; }
        .finance-account strong { color:#e5e5e5; font-size:.82rem; text-align:right; }
        .finance-account small { display:block; margin-top:.2rem; color:#737373; font-size:.67rem; font-weight:400; }
        .finance-entry { border:1px solid #292929; border-radius:9px; background:#0a0a0a; }
        .finance-entry summary { cursor:pointer; padding:.85rem 1rem; list-style:none; }
        .finance-entry summary::-webkit-details-marker { display:none; }
        .finance-entry summary::after { content:'+'; float:right; color:#4ade80; font-size:1.1rem; }
        .finance-entry[open] summary::after { content:'−'; }
        .finance-form { padding:0 1rem 1rem; }
        .finance-form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.8rem; }
        .finance-field { display:grid; gap:.35rem; color:#b3b3b3; font-size:.76rem; font-weight:700; }
        .finance-field input, .finance-field select, .finance-field textarea { width:100%; min-height:2.45rem; border:1px solid #333; border-radius:7px; padding:.5rem .6rem; color:#e5e5e5; background:#111; color-scheme:dark; }
        .finance-field input[type="radio"] { width:1rem; min-height:1rem; margin:0; accent-color:#22c55e; }
        .finance-field textarea { min-height:4.5rem; resize:vertical; }
        .finance-field-full { grid-column:1 / -1; }
        .finance-currency-options { display:flex; flex-wrap:wrap; gap:.55rem; }
        .finance-currency-option { display:flex; align-items:center; gap:.4rem; padding:.5rem .65rem; border:1px solid #333; border-radius:7px; background:#111; color:#e5e5e5; font-size:.75rem; }
        .finance-preview { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.6rem 1rem; margin-top:.85rem; padding:.85rem; border:1px solid #252525; border-radius:8px; background:#080808; }
        .finance-preview-item span { display:block; color:#737373; font-size:.67rem; }
        .finance-preview-item strong { display:block; margin-top:.2rem; color:#e5e5e5; font-size:.78rem; overflow-wrap:anywhere; }
        .finance-toolbar { display:flex; flex-wrap:wrap; align-items:end; gap:.6rem; }
        .finance-filters { display:flex; flex-wrap:wrap; align-items:end; gap:.6rem; }
        .finance-filters .finance-field { min-width:130px; }
        .finance-table-wrap { overflow-x:auto; }
        .finance-table { width:100%; border-collapse:collapse; text-align:left; font-size:.78rem; }
        .finance-table th { padding:.65rem; color:#8a8a8a; border-bottom:1px solid #292929; font-size:.66rem; text-transform:uppercase; }
        .finance-table td { padding:.7rem .65rem; color:#d4d4d4; border-bottom:1px solid #1f1f1f; vertical-align:top; }
        .finance-type { display:inline-flex; border-radius:99px; padding:.23rem .5rem; background:#172554; color:#bfdbfe; font-size:.67rem; font-weight:750; white-space:nowrap; }
        .finance-actions { display:flex; flex-wrap:wrap; align-items:center; gap:.55rem; }
        .finance-actions a { color:#4ade80; font-size:.75rem; font-weight:750; text-decoration:none; }
        .finance-delete { border:0; background:none; padding:0; color:#f87171; font-size:.75rem; font-weight:750; cursor:pointer; }
        .finance-error { color:#f87171; font-size:.72rem; }
        .finance-pagination { margin-top:1rem; color:#a3a3a3; }
        .finance-pagination nav { background:transparent; padding:0; }
        .finance-dark-layout .btn-action { min-height:2.25rem; padding:.45rem .75rem; border:1px solid #333; border-radius:7px; background:#151515; color:#e5e5e5; font-size:.76rem; }
        .finance-dark-layout .btn-action:hover { border-color:#16a34a; color:#4ade80; }
        @media(max-width:700px) {
            .finance-form-grid { grid-template-columns:1fr; }
            .finance-field-full { grid-column:auto; }
            .finance-accounts { grid-template-columns:1fr; }
        }
        @media(max-width:460px) {
            .finance-summary { grid-template-columns:1fr; }
            .finance-heading h1 { font-size:1.2rem; }
        }
    </style>

    <div class="finance-page">
        <header class="finance-heading">
            <div>
                <h1>Finanzas</h1>
                <p>Control financiero y movimientos de la concesionaria</p>
            </div>
            <span class="finance-badge"><i class="fas fa-chart-line"></i> Registros actuales</span>
        </header>

        <section class="finance-section" aria-labelledby="finance-summary-title" style="border-top:0;">
            <div class="finance-section-heading">
                <div>
                    <h2 id="finance-summary-title">Resumen financiero</h2>
                    <p>Conteo de registros guardados; no representa sumas monetarias.</p>
                </div>
            </div>
            <div class="finance-summary" aria-label="Resumen de movimientos">
                <article class="finance-stat">
                    <span>Movimientos de ingreso</span>
                    <strong class="is-positive">{{ number_format($conteoIngresos, 0, ',', '.') }}</strong>
                    <small>Registros almacenados</small>
                </article>
                <article class="finance-stat">
                    <span>Movimientos de egreso</span>
                    <strong>{{ number_format($conteoEgresos, 0, ',', '.') }}</strong>
                    <small>Gastos, impuestos y costos de venta</small>
                </article>
                <article class="finance-stat">
                    <span>Cuentas por cobrar</span>
                    <strong>{{ $conteoCobrar > 0 ? number_format($conteoCobrar, 0, ',', '.') : 'No disponible' }}</strong>
                    <small>{{ $conteoCobrar > 0 ? 'Registros sin saldo ni abonos asociados' : 'No hay registros de cuentas por cobrar' }}</small>
                </article>
                <article class="finance-stat">
                    <span>Cuentas por pagar</span>
                    <strong>{{ $conteoPagar > 0 ? number_format($conteoPagar, 0, ',', '.') : 'No disponible' }}</strong>
                    <small>{{ $conteoPagar > 0 ? 'Registros sin saldo ni vencimiento asociado' : 'No hay registros de cuentas por pagar' }}</small>
                </article>
            </div>
        </section>

        <div class="finance-notice" role="note">
            <strong>Importes no consolidados:</strong> los movimientos históricos pueden no tener moneda; no se les asigna una automáticamente. Los nuevos movimientos sí registran moneda y pueden asociarse a un vehículo. Aquí se muestran conteos y valores individuales, sin sumar ni convertir divisas. El sistema todavía no guarda saldos, pagos o vencimientos de cuentas.
        </div>

        <section class="finance-section" aria-labelledby="finance-chart-title">
            <div class="finance-section-heading">
                <div>
                    <h2 id="finance-chart-title">Movimientos registrados por mes</h2>
                    <p>Últimos seis meses · cantidad de registros, no valores monetarios</p>
                </div>
            </div>
            <div class="finance-chart">
                @if ($hayMovimientosMensuales)
                    <canvas
                        id="finance-monthly-chart"
                        aria-label="Gráfica mensual de cantidad de ingresos y egresos registrados"
                        data-labels="{{ json_encode($serieMensual['labels']) }}"
                        data-income="{{ json_encode($serieMensual['ingresos']) }}"
                        data-expense="{{ json_encode($serieMensual['egresos']) }}"
                    ></canvas>
                @else
                    <div class="finance-empty"><strong>Sin movimientos recientes</strong>Los registros financieros aparecerán aquí al guardarlos.</div>
                @endif
            </div>
        </section>

        <section class="finance-section" aria-labelledby="finance-accounts-title">
            <div class="finance-section-heading">
                <div>
                    <h2 id="finance-accounts-title">Cuentas por cobrar y pagar</h2>
                    <p>Los tipos de registros existen, pero faltan campos para calcular saldos.</p>
                </div>
            </div>
            <div class="finance-accounts">
                <div class="finance-account">
                    <span>Cuentas por cobrar<small>Sin cliente, abonos ni fecha de vencimiento asociados</small></span>
                    <strong>{{ $conteoCobrar }} registro(s)<small>Saldo pendiente: no disponible</small></strong>
                </div>
                <div class="finance-account">
                    <span>Cuentas por pagar<small>Sin proveedor, pagos ni fecha de vencimiento asociados</small></span>
                    <strong>{{ $conteoPagar }} registro(s)<small>Saldo pendiente: no disponible</small></strong>
                </div>
            </div>
        </section>

        <section class="finance-section" aria-label="Formulario de movimientos">
            <details class="finance-entry" @if ($formularioAbierto) open @endif>
                <summary>{{ $transaccion ? 'Editar movimiento financiero' : 'Registrar movimiento financiero' }}</summary>
                <form class="finance-form" method="post" action="{{ $transaccion ? route('finanzas.update', $transaccion) : route('finanzas.store') }}">
                    @csrf
                    @if ($transaccion)
                        @method('PUT')
                    @endif
                    <div class="finance-form-grid">
                        <label class="finance-field">
                            Tipo de movimiento
                            <select name="tipo" required>
                                <option value="">Selecciona un tipo</option>
                                @foreach ($tipos as $tipo => $etiqueta)
                                    <option value="{{ $tipo }}" @selected(old('tipo', $transaccion?->tipo) === $tipo)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            @error('tipo')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="finance-field">
                            Valor
                            <input type="number" name="monto" min="0.01" step="0.01" value="{{ old('monto', $transaccion?->monto) }}" required>
                            @error('monto')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                        <fieldset class="finance-field" style="border:0;padding:0;margin:0;">
                            <legend>Moneda</legend>
                            <div class="finance-currency-options">
                                @foreach (['COP' => 'COP', 'USD' => 'USD', 'EUR' => 'EUR'] as $codigo => $etiqueta)
                                    <label class="finance-currency-option">
                                        <input type="radio" name="moneda" value="{{ $codigo }}" @checked(old('moneda', $transaccion?->moneda) === $codigo) required>
                                        {{ $etiqueta }}
                                    </label>
                                @endforeach
                            </div>
                            @error('moneda')<span class="finance-error">{{ $message }}</span>@enderror
                        </fieldset>
                        <label class="finance-field">
                            Categoría
                            <input type="text" name="categoria" list="finance-categories" maxlength="50" value="{{ old('categoria', $transaccion?->categoria) }}" placeholder="Ej. Transporte internacional" required>
                            <datalist id="finance-categories">
                                <option value="Compra de vehículo">
                                <option value="Transporte internacional">
                                <option value="Seguro">
                                <option value="Aranceles e impuestos">
                                <option value="Gastos aduaneros">
                                <option value="Gastos administrativos">
                                <option value="Otros">
                            </datalist>
                            @error('categoria')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="finance-field">
                            Vehículo asociado
                            <span style="font-weight:400;color:#737373;">(obligatorio para registrar una venta de vehículo)</span>
                            <select name="vehiculo_id" id="finance-vehicle">
                                <option value="">Movimiento general</option>
                                @foreach ($vehiculos as $vehiculo)
                                    <option value="{{ $vehiculo->id }}" @selected((string) old('vehiculo_id', $transaccion?->vehiculo_id) === (string) $vehiculo->id)>
                                        {{ $vehiculo->marca }} {{ $vehiculo->modelo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('vehiculo_id')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="finance-field">
                            Fecha
                            <input type="date" name="fecha" value="{{ old('fecha', $transaccion?->fecha?->format('Y-m-d') ?? now()->toDateString()) }}" required>
                            @error('fecha')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                        <label class="finance-field finance-field-full">
                            Concepto u observación
                            <textarea name="descripcion" minlength="5" maxlength="500" required>{{ old('descripcion', $transaccion?->descripcion) }}</textarea>
                            @error('descripcion')<span class="finance-error">{{ $message }}</span>@enderror
                        </label>
                    </div>
                    <div class="finance-preview" aria-live="polite">
                        <div class="finance-preview-item">
                            <span>Vista previa del movimiento</span>
                            <strong id="finance-preview-type">{{ $tipos[old('tipo', $transaccion?->tipo)] ?? 'Tipo pendiente' }}</strong>
                        </div>
                        <div class="finance-preview-item">
                            <span>Valor</span>
                            <strong id="finance-preview-amount">Sin valor</strong>
                        </div>
                        <div class="finance-preview-item">
                            <span>Categoría y vehículo</span>
                            <strong id="finance-preview-category">Sin categoría · Movimiento general</strong>
                        </div>
                        <div class="finance-preview-item">
                            <span>Descripción</span>
                            <strong id="finance-preview-description">Sin descripción</strong>
                        </div>
                    </div>
                    @if ($errors->has('tipo') || $errors->has('monto') || $errors->has('moneda') || $errors->has('vehiculo_id') || $errors->has('descripcion') || $errors->has('fecha') || $errors->has('categoria') || $errors->has('transaccion'))
                        <p class="finance-error" role="alert">Revisa los campos marcados antes de guardar.</p>
                    @endif
                    @error('transaccion')<p class="finance-error" role="alert">{{ $message }}</p>@enderror
                    <div class="finance-actions" style="margin-top:.85rem;">
                        <button class="btn-action" type="submit">{{ $transaccion ? 'Guardar cambios' : 'Registrar movimiento' }}</button>
                        @if ($transaccion)
                            <a href="{{ route('finanzas.index', request()->query()) }}">Cancelar edición</a>
                        @endif
                    </div>
                </form>
            </details>
        </section>

        <section class="finance-section" aria-labelledby="finance-movements-title">
            <div class="finance-section-heading">
                <div>
                    <h2 id="finance-movements-title">Últimos movimientos</h2>
                    <p>{{ number_format($conteoTotal, 0, ',', '.') }} movimiento(s) registrados en total</p>
                </div>
                <form class="finance-filters" method="get" action="{{ route('finanzas.index') }}">
                    <label class="finance-field">
                        Tipo
                        <select name="tipo">
                            <option value="">Todos</option>
                            @foreach ($tipos as $tipo => $etiqueta)
                                <option value="{{ $tipo }}" @selected(($filtros['tipo'] ?? '') === $tipo)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="finance-field">
                        Desde
                        <input type="date" name="fecha_inicio" value="{{ $filtros['fecha_inicio'] ?? '' }}">
                    </label>
                    <label class="finance-field">
                        Hasta
                        <input type="date" name="fecha_fin" value="{{ $filtros['fecha_fin'] ?? '' }}">
                    </label>
                    <button class="btn-action" type="submit">Filtrar</button>
                    <a href="{{ route('finanzas.index') }}" style="color:#4ade80;font-size:.76rem;font-weight:750;text-decoration:none;">Limpiar</a>
                </form>
            </div>
            <div class="finance-table-wrap">
                <table class="finance-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Categoría y concepto</th>
                            <th>Vehículo</th>
                            <th>Monto registrado</th>
                            <th>Registrado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transacciones as $item)
                            <tr>
                                <td>{{ $item->fecha?->format('Y-m-d') }}</td>
                                <td><span class="finance-type">{{ $tipos[$item->tipo] ?? ucfirst(str_replace('_', ' ', $item->tipo)) }}</span></td>
                                <td><strong>{{ $item->categoria }}</strong><br>{{ $item->descripcion }}</td>
                                <td>{{ $item->vehiculo ? $item->vehiculo->marca.' '.$item->vehiculo->modelo : 'Movimiento general' }}</td>
                                <td>
                                    @if ($item->moneda)
                                        {{ $item->moneda }} {{ number_format((float) $item->monto, 2, ',', '.') }}
                                    @else
                                        <span>Sin moneda</span><br>{{ number_format((float) $item->monto, 2, ',', '.') }}
                                    @endif
                                </td>
                                <td>{{ $item->user?->name ?? 'Usuario no disponible' }}</td>
                                <td>
                                    <div class="finance-actions">
                                        <a href="{{ route('finanzas.edit', array_merge(['transaccion' => $item->id], request()->query())) }}">Editar</a>
                                        <form method="post" action="{{ route('finanzas.destroy', $item) }}" onsubmit="return confirm('¿Eliminar este movimiento financiero?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="finance-delete" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="finance-empty" colspan="7">No hay movimientos que coincidan con los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="finance-pagination">{{ $transacciones->links() }}</div>
        </section>
    </div>

    <script>
        window.addEventListener('load', () => {
            const financeChart = document.getElementById('finance-monthly-chart');
            if (financeChart) {
                new Chart(financeChart, {
                    type: 'bar',
                    data: {
                        labels: JSON.parse(financeChart.dataset.labels),
                        datasets: [
                            { label: 'Ingresos registrados', data: JSON.parse(financeChart.dataset.income), backgroundColor: '#2563eb', borderRadius: 4 },
                            { label: 'Egresos registrados', data: JSON.parse(financeChart.dataset.expense), backgroundColor: '#16a34a', borderRadius: 4 },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { labels: { color: '#a3a3a3' } } },
                        scales: {
                            x: { ticks: { color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                            y: { beginAtZero: true, ticks: { precision: 0, color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                        },
                    },
                });
            }

            const financeForm = document.querySelector('.finance-form');
            if (financeForm) {
                const typeInput = financeForm.querySelector('[name="tipo"]');
                const amountInput = financeForm.querySelector('[name="monto"]');
                const categoryInput = financeForm.querySelector('[name="categoria"]');
                const vehicleInput = financeForm.querySelector('[name="vehiculo_id"]');
                const descriptionInput = financeForm.querySelector('[name="descripcion"]');
                const amountPreview = document.getElementById('finance-preview-amount');

                const updateFinancePreview = () => {
                    const currency = financeForm.querySelector('[name="moneda"]:checked')?.value;
                    const amount = Number(amountInput.value);
                    const formatter = currency
                        ? new Intl.NumberFormat('es-CO', { style: 'currency', currency, maximumFractionDigits: 2 })
                        : null;

                    document.getElementById('finance-preview-type').textContent =
                        typeInput.options[typeInput.selectedIndex]?.text || 'Tipo pendiente';
                    amountPreview.textContent = Number.isFinite(amount) && amount > 0 && formatter
                        ? formatter.format(amount)
                        : 'Sin valor o moneda';
                    document.getElementById('finance-preview-category').textContent =
                        `${categoryInput.value || 'Sin categoría'} · ${vehicleInput.options[vehicleInput.selectedIndex]?.text || 'Movimiento general'}`;
                    document.getElementById('finance-preview-description').textContent =
                        descriptionInput.value || 'Sin descripción';
                };

                financeForm.addEventListener('input', updateFinancePreview);
                financeForm.addEventListener('change', updateFinancePreview);
                updateFinancePreview();
            }
        });
    </script>
@endsection
