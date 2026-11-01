@extends('layouts.admin')

@section('title', 'Dashboard financiero — Demostración')
@section('page_title', 'Panel financiero')
@section('page_subtitle', 'Ejemplo demostrativo con cifras ficticias')

@section('content')
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(170px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .dashboard-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.06);
            border-radius: 18px;
            padding: 1.2rem;
            box-shadow: 0 15px 35px rgba(23, 23, 31, 0.05);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 24px 45px rgba(23, 23, 31, 0.1);
        }

        .dashboard-card h3 {
            margin: 0 0 0.75rem;
            font-size: 1rem;
            color: #4b4b63;
        }

        .dashboard-card .value {
            font-size: 2rem;
            font-weight: 800;
            color: #161b3d;
        }

        .dashboard-card .label {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            color: #6b7280;
        }

        .dashboard-panels {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .chart-card {
            background: #ffffff;
            border-radius: 22px;
            padding: 1.3rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 18px 40px rgba(23, 23, 31, 0.05);
        }

        .chart-card h3 {
            margin: 0 0 1rem;
            font-size: 1.1rem;
            color: #21243d;
        }

        .chart-card canvas {
            width: 100% !important;
            min-height: 320px !important;
            max-height: 360px !important;
            display: block;
        }

        .chart-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .chart-summary {
            background: #f8fbff;
            border-radius: 16px;
            padding: 1rem;
            border: 1px solid rgba(59, 130, 246, 0.12);
            color: #1e3a8a;
        }

        .chart-summary strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 0.35rem;
        }

        .chart-summary span {
            font-size: 1.4rem;
            font-weight: 700;
        }

        .data-table-wrap {
            overflow-x: auto;
            margin-top: 0.75rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 560px;
        }

        .data-table th,
        .data-table td {
            text-align: left;
            padding: 0.85rem 0.9rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        }

        .data-table th {
            color: #384464;
            font-weight: 700;
            background: #f7f9ff;
        }

        .data-table tbody tr:hover {
            background: rgba(59, 130, 246, 0.04);
        }

        .chart-footer {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .chart-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.08);
            color: #1d4ed8;
            font-size: 0.95rem;
        }

        .chart-pill span {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
        }

        .label-costos span { background: #3b82f6; }
        .label-gastos span { background: #f97316; }
        .label-impuestos span { background: #ef4444; }
        .label-utilidad span { background: #10b981; }

        @media (max-width: 1024px) {
            .dashboard-grid,
            .dashboard-panels {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="toolbar">
        <h2>Resumen financiero</h2>
        <div>
            <a href="{{ route('catalogo') }}" class="btn-ghost">Catálogo</a>
            <a href="{{ route('internacional.vehiculos.index') }}" class="btn-ghost">Comercio internacional</a>
        </div>
    </div>

    <div role="status" style="padding:0.9rem 1rem; margin-bottom:1.25rem; border:1px solid #fcd34d; border-radius:12px; background:#fffbeb; color:#92400e;">
        <strong>Modo demostración:</strong> cifras ficticias e ilustrativas en COP; no se guardan ni representan movimientos reales.
    </div>

    @php
        $costosVentas = $estado['costos_ventas'];
        $gastosOperativos = $estado['gastos_operativos'];
        $impuestos = $estado['impuestos'];
        $ingresos = $estado['ingresos'];
        $utilidadBruta = $estado['utilidad_bruta'];
        $utilidadOperativa = $estado['utilidad_operativa'];
        $utilidadNeta = $estado['utilidad_neta'];

        $gastosTotales = $costosVentas + $gastosOperativos + $impuestos;
    @endphp

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Gastos Totales</h3>
            <div class="value">${{ number_format($gastosTotales, 2) }}</div>
            <div class="label">Costos y obligaciones del periodo actual</div>
        </div>
        <div class="dashboard-card">
            <h3>Gasto de Impuestos</h3>
            <div class="value">${{ number_format($impuestos, 2) }}</div>
            <div class="label">Total pagado en impuestos</div>
        </div>
        <div class="dashboard-card">
            <h3>Gasto de Operaciones</h3>
            <div class="value">${{ number_format($gastosOperativos, 2) }}</div>
            <div class="label">Gastos operativos del negocio</div>
        </div>
        <div class="dashboard-card">
            <h3>Ganancia Neta</h3>
            <div class="value">${{ number_format($utilidadNeta, 2) }}</div>
            <div class="label">Utilidad después de costos e impuestos</div>
        </div>
    </div>

    <div class="dashboard-panels">
        <div class="chart-card">
            <h3>Distribución de costos</h3>
            <canvas id="costBreakdownChart" height="280"></canvas>
            @if($costosVentas + $gastosOperativos + $impuestos <= 0)
                <p style="margin-top:0.75rem; color:#6b7280; font-size:0.95rem;">No hay costos ni gastos registrados para este periodo.</p>
            @endif
            <div class="chart-footer">
                <div class="chart-pill label-costos"><span></span> Costos de venta</div>
                <div class="chart-pill label-gastos"><span></span> Gastos operativos</div>
                <div class="chart-pill label-impuestos"><span></span> Impuestos</div>
            </div>
        </div>

        <div class="chart-card">
            <h3>Utilidad vs Gastos</h3>
            <canvas id="profitVsExpenseChart" height="280"></canvas>
            @if($ingresos + $gastosTotales <= 0)
                <p style="margin-top:0.75rem; color:#6b7280; font-size:0.95rem;">No hay ingresos ni gastos registrados para este periodo.</p>
            @endif
            <div class="chart-list">
                <div class="chart-summary">
                    <strong>Ingresos</strong>
                    <span>${{ number_format($ingresos, 2) }}</span>
                </div>
                <div class="chart-summary">
                    <strong>Utilidad operativa</strong>
                    <span>${{ number_format($utilidadOperativa, 2) }}</span>
                </div>
            </div>
            <div class="chart-footer">
                <div class="chart-pill label-utilidad"><span></span> Utilidad neta</div>
                <div class="chart-pill label-gastos"><span></span> Total gastos</div>
            </div>
        </div>
    </div>

    <div class="card-panel" style="padding:1.25rem; margin-bottom:1rem;">
        <h3>Perfil de ingresos y resultados</h3>
        <div style="display:grid; grid-template-columns: repeat(3, minmax(160px, 1fr)); gap:1rem; margin-top:1rem;">
            <div style="background:#f0f9ff; border-radius:16px; padding:1rem;">
                <strong>Utilidad bruta</strong>
                <div style="font-size:1.6rem; font-weight:800; margin-top:0.6rem;">${{ number_format($utilidadBruta, 2) }}</div>
            </div>
            <div style="background:#fef3c7; border-radius:16px; padding:1rem;">
                <strong>Costos de venta</strong>
                <div style="font-size:1.6rem; font-weight:800; margin-top:0.6rem;">${{ number_format($costosVentas, 2) }}</div>
            </div>
            <div style="background:#ede9fe; border-radius:16px; padding:1rem;">
                <strong>Ingresos</strong>
                <div style="font-size:1.6rem; font-weight:800; margin-top:0.6rem;">${{ number_format($ingresos, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card-panel" style="padding:1.25rem; margin-bottom:1rem;">
        <h3>Cuentas por Pagar</h3>
        <div class="data-table-wrap" style="margin-top:0.75rem;">
            <table class="data-table">
                <thead>
                    <tr><th>Descripción</th><th>Monto</th><th>Fecha</th></tr>
                </thead>
                <tbody>
                    @forelse($cxp as $t)
                        <tr>
                            <td>{{ $t->descripcion ?? '-' }}</td>
                            <td>${{ number_format($t->monto,2) }}</td>
                            <td>{{ $t->created_at instanceof \Illuminate\Support\Carbon ? $t->created_at->format('Y-m-d') : $t->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No hay cuentas por pagar registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-panel" style="padding:1.25rem; margin-bottom:1rem;">
        <h3>Cuentas por Cobrar</h3>
        <div class="data-table-wrap" style="margin-top:0.75rem;">
            <table class="data-table">
                <thead>
                    <tr><th>Cliente</th><th>Monto</th><th>Vencimiento</th></tr>
                </thead>
                <tbody>
                    @forelse($cxc as $t)
                        <tr>
                            <td>{{ $t->descripcion ?? '-' }}</td>
                            <td>${{ number_format($t->monto,2) }}</td>
                            <td>{{ $t->created_at instanceof \Illuminate\Support\Carbon ? $t->created_at->format('Y-m-d') : $t->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No hay cuentas por cobrar registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-panel" style="padding:1.25rem; margin-bottom:1rem;">
        <h3>Informes financieros de demostración</h3>
        <div class="data-table-wrap" style="margin-top:0.75rem;">
            <table class="data-table">
                <thead>
                    <tr><th>Tipo</th><th>Periodo</th><th>Archivo</th></tr>
                </thead>
                <tbody>
                    @forelse($informes as $inf)
                        <tr>
                            <td>{{ $inf->tipo }}</td>
                            <td>{{ $inf->periodo }}</td>
                            <td><a href="{{ asset('storage/'.$inf->ruta) }}" target="_blank">{{ $inf->nombre_archivo }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3">No se cargan informes reales en este panel de demostración.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const costData = [
            {{ $costosVentas }},
            {{ $gastosOperativos }},
            {{ $impuestos }}
        ];

        const profitData = [
            {{ $ingresos }},
            {{ $gastosTotales }},
            {{ $utilidadNeta }}
        ];

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                tooltip: { callbacks: { label: ctx => ctx.label + ': $' + ctx.parsed.toLocaleString('es-AR', {minimumFractionDigits:2}) } }
            },
            animation: { duration: 900, easing: 'easeOutQuart' }
        };

        new Chart(document.getElementById('costBreakdownChart'), {
            type: 'doughnut',
            data: {
                labels: ['Costos de venta', 'Gastos operativos', 'Impuestos'],
                datasets: [{
                    data: costData,
                    backgroundColor: ['#3b82f6', '#f97316', '#ef4444'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 12,
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('profitVsExpenseChart'), {
            type: 'pie',
            data: {
                labels: ['Ingresos', 'Total Gastos', 'Utilidad Neta'],
                datasets: [{
                    data: profitData,
                    backgroundColor: ['#2563eb', '#fb923c', '#10b981'],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 12,
                }]
            },
            options: chartOptions
        });
    </script>
@endsection
