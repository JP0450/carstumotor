@extends('layouts.admin')

@section('title', 'Dashboard gerencial')
@section('body_class', 'manager-dark-layout')

@section('content')
    @php
        $tarjetasPrincipales = [
            ['titulo' => 'Ingresos del periodo', 'clave' => 'ingresos', 'formato' => 'dinero'],
            ['titulo' => 'Utilidad neta', 'clave' => 'utilidad_neta', 'formato' => 'dinero'],
            ['titulo' => 'Vehículos disponibles en inventario', 'clave' => 'inventario_disponible', 'formato' => 'cantidad'],
            ['titulo' => 'Vehículos vendidos', 'clave' => 'vehiculos_vendidos', 'formato' => 'cantidad'],
        ];
        $indicadoresAdicionales = [
            ['titulo' => 'Importaciones en curso', 'clave' => 'importaciones_en_curso', 'formato' => 'cantidad'],
            ['titulo' => 'Importe registrado por cobrar', 'clave' => 'cuentas_por_cobrar', 'formato' => 'dinero'],
            ['titulo' => 'Importe registrado por pagar', 'clave' => 'cuentas_por_pagar', 'formato' => 'dinero'],
            ['titulo' => 'Margen promedio', 'clave' => 'margen_promedio', 'formato' => 'porcentaje'],
        ];
        $valorTarjeta = function (array $tarjeta) use ($dashboard): string {
            $kpi = $dashboard['kpis'][$tarjeta['clave']];

            return match ($tarjeta['formato']) {
                'dinero' => $dashboard['periodo']['moneda'].' '.number_format((float) ($kpi['valor'] ?? 0), 2, ',', '.'),
                'porcentaje' => number_format((float) $kpi['valor'], 2, ',', '.').'%',
                default => number_format((float) ($kpi['valor'] ?? 0), 0, ',', '.'),
            };
        };
        $hayInventario = $dashboard['inventario']['registros'] > 0;
    @endphp

    <style>
        .manager-dark-layout { background:#050505!important; color:#e5e7eb; }
        .manager-dark-layout .page-header { display:none; }
        .manager-dark-layout .admin-main { width:100%; max-width:none; margin:0; padding:1.25rem clamp(1rem, 3vw, 2.5rem) 3rem; }
        .manager-dark-layout footer { border-top:1px solid #202020; background:#050505; color:#737373; }
        .manager-dashboard { --ink:#f4f4f5; --muted:#a3a3a3; --line:#262626; --green:#22c55e; max-width:1120px; margin:0 auto; }
        .manager-heading { display:flex; flex-wrap:wrap; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.15rem; }
        .manager-heading h1 { margin:0; color:#f5f5f5; font-size:1.35rem; line-height:1.3; font-weight:750; }
        .manager-heading p { margin:.25rem 0 0; color:#a3a3a3; font-size:.88rem; }
        .manager-badge { display:inline-flex; align-items:center; gap:.4rem; border-radius:99px; padding:.28rem .7rem; background:#052e16; color:#4ade80; font-size:.75rem; font-weight:750; }
        .manager-toolbar { display:grid; gap:.65rem; margin-bottom:1.15rem; }
        .manager-toolbar h2, .manager-section h2 { margin:0; color:var(--ink); font-size:1.05rem; font-weight:800; }
        .manager-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
        .manager-actions a { display:inline-flex; align-items:center; gap:.45rem; border:1px solid #303030; border-radius:8px; padding:.48rem .68rem; color:#d4d4d4; font-size:.76rem; font-weight:700; text-decoration:none; }
        .manager-actions a:hover { border-color:#16a34a; color:#4ade80; }
        .manager-filters { display:flex; flex-wrap:wrap; align-items:end; gap:.65rem; margin:.2rem 0 1.4rem; padding:0; }
        .manager-filters label { display:grid; gap:.3rem; color:#a3a3a3; font-size:.72rem; font-weight:700; }
        .manager-filters input, .manager-filters select { min-height:2.25rem; border:1px solid #333; border-radius:7px; padding:.4rem .55rem; color:#e5e5e5; background:#111; color-scheme:dark; }
        .manager-filter-range { display:none !important; }
        .manager-filter-range.is-visible { display:grid !important; }
        .manager-kpis { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1.5rem 2rem; padding-bottom:1.5rem; }
        .manager-kpi { min-width:0; padding:.4rem .85rem .85rem; border-bottom:1px solid #202020; }
        .manager-kpi h3 { margin:0; color:#b3b3b3; font-size:.82rem; font-weight:500; line-height:1.45; }
        .manager-kpi-value { margin:.3rem 0 .08rem; color:var(--ink); font-size:1.65rem; font-weight:750; overflow-wrap:anywhere; }
        .manager-kpi-value.is-available { color:#4ade80; }
        .manager-kpi p { margin:0; color:var(--muted); font-size:.73rem; line-height:1.45; }
        .manager-kpi p.is-positive { color:#4ade80; }
        .manager-note { margin:0 0 1.5rem; padding:.7rem .85rem; border-left:2px solid #16a34a; background:#0b0b0b; color:#a3a3a3; font-size:.75rem; line-height:1.5; }
        .manager-additional { padding:1.15rem 0 1.35rem; border-top:1px solid #292929; border-bottom:1px solid #292929; }
        .manager-additional h2 { margin:0 0 .55rem; color:#f4f4f5; font-size:1rem; font-weight:750; }
        .manager-additional-list { display:grid; }
        .manager-additional-row { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.25rem 1rem; padding:.52rem 0; }
        .manager-additional-label { color:#b3b3b3; font-size:.88rem; }
        .manager-additional-value { color:#e5e5e5; font-size:.98rem; font-weight:750; text-align:right; }
        .manager-additional-note { flex-basis:100%; color:#737373; font-size:.7rem; line-height:1.4; }
        .manager-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0 2rem; margin:1.2rem 0; }
        .manager-section { min-width:0; padding:1rem 0; border-bottom:1px solid #262626; }
        .manager-section header { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:start; gap:.5rem; margin-bottom:.75rem; }
        .manager-section header p { margin:.25rem 0 0; color:var(--muted); font-size:.72rem; }
        .manager-chart-wrap { height:250px; }
        .manager-empty { display:grid; min-height:130px; place-content:center; padding:1rem; border:1px dashed #333; border-radius:9px; color:var(--muted); text-align:center; font-size:.8rem; line-height:1.5; }
        .manager-empty strong { display:block; margin-bottom:.3rem; color:#e5e5e5; }
        .manager-alerts { display:grid; gap:.55rem; }
        .manager-alert { display:flex; align-items:center; gap:.7rem; padding:.65rem .7rem; border:1px solid #303030; border-radius:8px; color:#d4d4d4; font-size:.78rem; }
        .manager-alert i { color:#fbbf24; }
        .manager-alert.good i { color:#4ade80; }
        .manager-coverage { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.55rem 1.2rem; margin:0; padding-left:1.2rem; color:#a3a3a3; font-size:.76rem; line-height:1.55; }
        .manager-coverage li { padding-left:.15rem; }
        .manager-inventory-summary { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.7rem; margin:.6rem 0; }
        .manager-inventory-summary div { padding:.7rem; border-radius:8px; background:#111; }
        .manager-inventory-summary strong { display:block; color:var(--ink); font-size:1.1rem; }
        .manager-inventory-summary span { color:var(--muted); font-size:.7rem; }
        .manager-scope { margin:1rem 0 0; color:var(--muted); font-size:.7rem; }
        .manager-dark-layout .btn-action { min-height:2.25rem; padding:.45rem .75rem; border-radius:7px; font-size:.78rem; }
        .manager-dark-layout canvas { color:#a3a3a3; }
        @media(max-width:680px) {
            .manager-grid { grid-template-columns:1fr; }
            .manager-coverage { grid-template-columns:1fr; }
            .manager-kpis { gap:1rem; }
        }
        @media(max-width:460px) {
            .manager-kpis { grid-template-columns:1fr; gap:.25rem; }
            .manager-heading h1 { font-size:1.2rem; }
        }
    </style>

    <div class="manager-dashboard">
        <header class="manager-heading">
            <div>
                <h1>Dashboard gerencial</h1>
                <p>Resumen general · {{ $dashboard['periodo']['etiqueta'] }}</p>
            </div>
            <span class="manager-badge"><i class="fas fa-chart-line"></i> Panel de consulta</span>
        </header>

        <section class="manager-toolbar" aria-label="Acciones del dashboard">
            <div class="manager-actions">
                <a href="{{ route('admin.dashboard.gerencial.pdf', request()->query()) }}"><i class="fas fa-file-pdf"></i> Informe PDF</a>
                <a href="{{ route('admin.dashboard.gerencial.csv', request()->query()) }}"><i class="fas fa-file-csv"></i> Abrir en Excel (CSV)</a>
                <a href="{{ route('finanzas.index') }}"><i class="fas fa-coins"></i> Finanzas y contabilidad</a>
                <a href="{{ route('internacional.vehiculos.index') }}"><i class="fas fa-globe"></i> Importaciones</a>
            </div>
        </section>

        <form class="manager-filters" method="get" action="{{ route('admin.dashboard.gerencial') }}">
            <label>
                Periodo
                <select id="manager-period" name="periodo">
                    @foreach ($periodos as $clave => $etiqueta)
                        <option value="{{ $clave }}" @selected($dashboard['periodo']['clave'] === $clave)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                Moneda
                <select name="moneda">
                    @foreach (['COP', 'USD', 'EUR'] as $moneda)
                        <option value="{{ $moneda }}" @selected($dashboard['periodo']['moneda'] === $moneda)>{{ $moneda }}</option>
                    @endforeach
                </select>
            </label>
            <label id="manager-start-wrap" class="manager-filter-range {{ $dashboard['periodo']['esPersonalizado'] ? 'is-visible' : '' }}">
                Desde
                <input id="manager-start" type="date" name="fecha_inicio" value="{{ $dashboard['periodo']['inicio'] }}">
            </label>
            <label id="manager-end-wrap" class="manager-filter-range {{ $dashboard['periodo']['esPersonalizado'] ? 'is-visible' : '' }}">
                Hasta
                <input id="manager-end" type="date" name="fecha_fin" value="{{ $dashboard['periodo']['fin'] }}">
            </label>
            <button type="submit" class="btn-action"><i class="fas fa-filter"></i> Aplicar</button>
            <span style="color:#64748b;font-size:.74rem;">{{ $dashboard['periodo']['inicio'] }} — {{ $dashboard['periodo']['fin'] }}</span>
        </form>

        <section class="manager-kpis" aria-label="Indicadores principales">
            @foreach ($tarjetasPrincipales as $tarjeta)
                @php($kpi = $dashboard['kpis'][$tarjeta['clave']])
                <article class="manager-kpi">
                    <h3>{{ $tarjeta['titulo'] }}</h3>
                    <div class="manager-kpi-value {{ $kpi['valor'] !== null ? 'is-available' : '' }}">{{ $valorTarjeta($tarjeta) }}</div>
                    <p>{{ $kpi['nota'] }}</p>
                </article>
            @endforeach
        </section>

        <div class="manager-note" role="note">
            <strong>Alcance de los datos:</strong> este panel usa los movimientos guardados en finanzas; los registros marcados DEMO son datos de ejemplo. Los importes se filtran por {{ $dashboard['periodo']['moneda'] }} y no se convierten. Los movimientos sin moneda se excluyen. La utilidad es ingresos menos egresos registrados y no sustituye un cierre contable; los importes por cobrar/pagar son registros brutos, no saldos.
        </div>

        <section class="manager-additional" aria-labelledby="manager-additional-title">
            <h2 id="manager-additional-title">Indicadores adicionales</h2>
            <div class="manager-additional-list">
                @foreach ($indicadoresAdicionales as $tarjeta)
                    @php($kpi = $dashboard['kpis'][$tarjeta['clave']])
                    <div class="manager-additional-row">
                        <span class="manager-additional-label">{{ $tarjeta['titulo'] }}</span>
                        <strong class="manager-additional-value">{{ $valorTarjeta($tarjeta) }}</strong>
                        <span class="manager-additional-note">{{ $kpi['nota'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="manager-grid">
            <section class="manager-section">
                <header>
                    <div>
                        <h2>Ingresos y egresos</h2>
                        <p>{{ $dashboard['finanzas']['registrosIngresos'] }} ingreso(s) · {{ $dashboard['finanzas']['registrosEgresos'] }} egreso(s) registrados; importes mensuales en {{ $dashboard['periodo']['moneda'] }}.</p>
                    </div>
                </header>
                @if ($dashboard['graficas']['finanzasDisponible'])
                    <div class="manager-chart-wrap">
                        <canvas
                            id="manager-finance-chart"
                            data-chart="financial"
                            data-labels='@json($dashboard['finanzas']['serie']['labels'])'
                            data-income='@json($dashboard['finanzas']['serie']['ingresos'])'
                            data-expense='@json($dashboard['finanzas']['serie']['egresos'])'
                            data-currency="{{ $dashboard['finanzas']['moneda'] }}"
                            aria-label="Gráfica de ingresos y egresos mensuales en {{ $dashboard['finanzas']['moneda'] }}"
                        ></canvas>
                    </div>
                @else
                    <div class="manager-empty"><strong>Sin movimientos en {{ $dashboard['finanzas']['moneda'] }}</strong>Registra ingresos o egresos en esta moneda para el periodo seleccionado.</div>
                @endif
            </section>

            <section class="manager-section">
                <header>
                    <div>
                        <h2>Ventas de vehículos</h2>
                        <p>Movimientos clasificados como venta de vehículo en el periodo seleccionado.</p>
                    </div>
                </header>
                @if ($dashboard['graficas']['ventasDisponible'])
                    <div class="manager-chart-wrap">
                        <canvas
                            id="manager-sales-chart"
                            data-chart="sales"
                            data-labels='@json($dashboard['finanzas']['ventas']['labels'])'
                            data-values='@json($dashboard['finanzas']['ventas']['cantidades'])'
                            aria-label="Vehículos vendidos por mes"
                        ></canvas>
                    </div>
                @else
                    <div class="manager-empty"><strong>Sin ventas registradas</strong>Al confirmar una venta, sus vehículos aparecerán en este gráfico.</div>
                @endif
            </section>

            <section class="manager-section">
                <header>
                    <div>
                        <h2>Rentabilidad por marca</h2>
                        <p>Venta menos costo registrado por marca en {{ $dashboard['periodo']['moneda'] }}.</p>
                    </div>
                </header>
                @if ($dashboard['graficas']['marcasDisponible'])
                    <div class="manager-chart-wrap">
                        <canvas
                            id="manager-brand-chart"
                            data-chart="brands"
                            data-labels='@json($dashboard['finanzas']['rentabilidadMarcas']['labels'])'
                            data-income='@json($dashboard['finanzas']['rentabilidadMarcas']['ingresos'])'
                            data-profit='@json($dashboard['finanzas']['rentabilidadMarcas']['utilidad'])'
                            data-currency="{{ $dashboard['finanzas']['moneda'] }}"
                            aria-label="Ingresos y utilidad por marca"
                        ></canvas>
                    </div>
                @else
                    <div class="manager-empty"><strong>Sin ventas vinculadas a vehículos</strong>Registra una venta asociada a un vehículo para comparar las marcas.</div>
                @endif
            </section>

            <section class="manager-section">
                <header>
                    <div>
                        <h2>Estado del inventario</h2>
                        <p>Unidades registradas en stock; no representa ventas ni reservas.</p>
                    </div>
                </header>
                @if ($hayInventario)
                    <div class="manager-inventory-summary">
                        <div><strong>{{ number_format($dashboard['inventario']['disponibles'], 0, ',', '.') }}</strong><span>Disponibles en catálogo</span></div>
                        <div><strong>{{ number_format($dashboard['inventario']['noDisponibles'], 0, ',', '.') }}</strong><span>No disponibles en catálogo</span></div>
                    </div>
                    <div class="manager-chart-wrap">
                        <canvas
                            id="manager-inventory-chart"
                            data-chart="inventory"
                            data-available="{{ $dashboard['inventario']['disponibles'] }}"
                            data-unavailable="{{ $dashboard['inventario']['noDisponibles'] }}"
                            aria-label="Distribución de inventario por disponibilidad de catálogo"
                        ></canvas>
                    </div>
                @else
                    <div class="manager-empty"><strong>Inventario sin registros físicos</strong>Agrega unidades en el módulo de inventario para actualizar esta sección.</div>
                @endif
                <p class="manager-scope">El catálogo solo indica si un vehículo está publicado como disponible; no distingue vendido, reservado o importándose.</p>
            </section>

            <section class="manager-section">
                <header>
                    <div>
                        <h2>Alertas gerenciales</h2>
                        <p>Condiciones verificables en los registros actuales.</p>
                    </div>
                </header>
                <div class="manager-alerts">
                    @if ($dashboard['alertas']['importacionesRetrasadas'] > 0)
                        <div class="manager-alert"><i class="fas fa-triangle-exclamation"></i> {{ $dashboard['alertas']['importacionesRetrasadas'] }} pedido(s) superaron su fecha estimada y no figuran como entregados.</div>
                    @endif
                    @if ($dashboard['alertas']['stockBajo'] > 0)
                        <div class="manager-alert"><i class="fas fa-boxes-stacked"></i> {{ $dashboard['alertas']['stockBajo'] }} registro(s) de inventario están en el umbral de stock bajo (3 unidades o menos).</div>
                    @endif
                    @if ($dashboard['alertas']['importacionesRetrasadas'] === 0 && $dashboard['alertas']['stockBajo'] === 0)
                        <div class="manager-alert good"><i class="fas fa-circle-check"></i> No se detectan alertas en pedidos atrasados ni en registros de stock bajo.</div>
                    @endif
                    <div class="manager-alert"><i class="fas fa-circle-info"></i> Los pagos vencidos requieren fechas y saldos; este módulo todavía no guarda esos campos.</div>
                </div>
            </section>

            <section class="manager-section">
                <header>
                    <div>
                        <h2>Informes disponibles</h2>
                        <p>Exportación resumida para el periodo seleccionado, sin datos personales.</p>
                    </div>
                </header>
                <div class="manager-actions">
                    <a href="{{ route('admin.dashboard.gerencial.pdf', request()->query()) }}"><i class="fas fa-file-pdf"></i> Descargar PDF</a>
                    <a href="{{ route('admin.dashboard.gerencial.csv', request()->query()) }}"><i class="fas fa-file-excel"></i> Exportar CSV para Excel</a>
                </div>
                <p class="manager-scope">Las ventas se identifican con movimientos asociados a vehículos; los importes por cobrar/pagar son totales registrados, no saldos pendientes.</p>
            </section>
        </div>

        <section class="manager-section">
            <header><div><h2>Cobertura de datos</h2><p>Alcance de los módulos actualmente disponibles en la base.</p></div></header>
            <ul class="manager-coverage">
                <li>Ingresos y egresos: importes por fecha y moneda seleccionada; no se convierten divisas.</li>
                <li>Inventario: unidades solo cuando existe un registro de stock asociado al vehículo.</li>
                <li>Importaciones: se cuentan pedidos; todavía no se asocian a un vehículo.</li>
                <li>Ventas: ingresos clasificados como venta de vehículo; el conteo y el historial permanecen aunque se elimine el vehículo.</li>
                <li>Cuentas por cobrar/pagar: total de registros clasificados; no se almacenan abonos, saldos ni vencimientos.</li>
                <li>Utilidad neta: resultado de ingresos menos egresos en la moneda seleccionada; no equivale a cierre contable.</li>
                <li>Margen por marca: ventas menos costos vinculados al vehículo.</li>
            </ul>
        </section>
    </div>

    <script>
        const periodSelect = document.getElementById('manager-period');
        const startWrap = document.getElementById('manager-start-wrap');
        const endWrap = document.getElementById('manager-end-wrap');
        const customRange = () => {
            const visible = periodSelect.value === 'personalizado';
            startWrap.classList.toggle('is-visible', visible);
            endWrap.classList.toggle('is-visible', visible);
            document.getElementById('manager-start').required = visible;
            document.getElementById('manager-end').required = visible;
        };
        periodSelect.addEventListener('change', customRange);
        customRange();
    </script>

    <script>
        window.addEventListener('load', () => {
        const monthLabels = @json($dashboard['finanzas']['serie']['labels']);
        const currencyCode = @json($dashboard['periodo']['moneda']);
        const moneyFormat = new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: currencyCode,
            maximumFractionDigits: 0,
        });
        const createManagerChart = (id, config) => {
            const canvas = document.getElementById(id);
            if (canvas) {
                new Chart(canvas, config);
            }
        };

        createManagerChart('manager-finance-chart', {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: [
                    { label: `Ingresos ${currencyCode}`, data: @json($dashboard['finanzas']['serie']['ingresos']), backgroundColor: '#0f766e', borderRadius: 4 },
                    { label: `Egresos ${currencyCode}`, data: @json($dashboard['finanzas']['serie']['egresos']), backgroundColor: '#64748b', borderRadius: 4 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: context => `${context.dataset.label}: ${moneyFormat.format(context.raw)}` } } },
                scales: {
                    x: { ticks: { color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                    y: { beginAtZero: true, ticks: { color: '#a3a3a3', callback: value => moneyFormat.format(value) }, grid: { color: '#1f1f1f' } },
                },
            },
        });

        createManagerChart('manager-sales-chart', {
            type: 'bar',
            data: {
                labels: @json($dashboard['finanzas']['ventas']['labels']),
                datasets: [{ label: 'Vehículos vendidos', data: @json($dashboard['finanzas']['ventas']['cantidades']), backgroundColor: '#2563eb', borderRadius: 4 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                },
            },
        });

        createManagerChart('manager-brand-chart', {
            type: 'bar',
            data: {
                labels: @json($dashboard['finanzas']['rentabilidadMarcas']['labels']),
                datasets: [
                    { label: `Ingresos ${currencyCode}`, data: @json($dashboard['finanzas']['rentabilidadMarcas']['ingresos']), backgroundColor: '#2563eb', borderRadius: 4 },
                    { label: `Utilidad ${currencyCode}`, data: @json($dashboard['finanzas']['rentabilidadMarcas']['utilidad']), backgroundColor: '#16a34a', borderRadius: 4 },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: context => context.raw === null ? `${context.dataset.label}: Costo no disponible en ${currencyCode}` : `${context.dataset.label}: ${moneyFormat.format(context.raw)}` } } },
                scales: {
                    x: { ticks: { color: '#a3a3a3' }, grid: { color: '#1f1f1f' } },
                    y: { ticks: { color: '#a3a3a3', callback: value => moneyFormat.format(value) }, grid: { color: '#1f1f1f' } },
                },
            },
        });

        @if ($hayInventario)
            createManagerChart('manager-inventory-chart', {
                    type: 'doughnut',
                    data: {
                        labels: ['Disponibles en catálogo', 'No disponibles en catálogo'],
                        datasets: [{ data: [{{ $dashboard['inventario']['disponibles'] }}, {{ $dashboard['inventario']['noDisponibles'] }}], backgroundColor: ['#0f766e', '#94a3b8'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
        @endif
        });
    </script>
@endsection
