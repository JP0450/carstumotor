@extends('layouts.admin')

@section('title', 'Dashboard — Contador')
@section('page_title', 'Panel del Contador')
@section('page_subtitle', 'Resumen financiero y herramientas contables — periodo actual')

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
            <a href="#" class="btn-ghost">Exportar</a>
            <a href="{{ route('admin.vehiculos.index') }}" class="btn-ghost">Vehículos</a>
        </div>
    </div>

    <div class="report-actions" style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; margin-bottom:1.5rem;">
        <form method="GET" action="{{ route('admin.finanzas.reporte.pdf') }}" style="display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
            <label style="display:flex; flex-direction:column; font-size:0.9rem; color:#334155;">
                Inicio
                <input type="date" name="inicio" value="{{ now()->startOfMonth()->toDateString() }}" style="border:1px solid #cbd5e1; border-radius:8px; padding:0.5rem;" />
            </label>
            <label style="display:flex; flex-direction:column; font-size:0.9rem; color:#334155;">
                Fin
                <input type="date" name="fin" value="{{ now()->endOfMonth()->toDateString() }}" style="border:1px solid #cbd5e1; border-radius:8px; padding:0.5rem;" />
            </label>
            <button type="submit" class="btn btn-primary" style="padding:0.75rem 1.2rem;">Descargar reporte PDF</button>
        </form>
        <span style="color:#64748b; font-size:0.95rem;">Descarga el reporte financiero para el periodo seleccionado.</span>
    </div>

    @php
        $sampleCostosVentas = 52000;
        $sampleGastosOperativos = 26000;
        $sampleImpuestos = 14000;
        $sampleIngresos = 150000;
        $sampleUtilidadBruta = 45000;
        $sampleUtilidadOperativa = 19000;
        $sampleUtilidadNeta = 7000;

        $costosVentas = $estado['costos_ventas'] > 0 ? $estado['costos_ventas'] : $sampleCostosVentas;
        $gastosOperativos = $estado['gastos_operativos'] > 0 ? $estado['gastos_operativos'] : $sampleGastosOperativos;
        $impuestos = $estado['impuestos'] > 0 ? $estado['impuestos'] : $sampleImpuestos;
        $ingresos = $estado['ingresos'] > 0 ? $estado['ingresos'] : $sampleIngresos;
        $utilidadBruta = $estado['utilidad_bruta'] !== 0 ? $estado['utilidad_bruta'] : $sampleUtilidadBruta;
        $utilidadOperativa = $estado['utilidad_operativa'] !== 0 ? $estado['utilidad_operativa'] : $sampleUtilidadOperativa;
        $utilidadNeta = $estado['utilidad_neta'] !== 0 ? $estado['utilidad_neta'] : $sampleUtilidadNeta;

        $gastosTotales = $costosVentas + $gastosOperativos + $impuestos;
        $valorReal = max($flujo['entradas'], 1);
        $profitRatio = $flujo['flujo_neto'] / $valorReal * 100;

        $useSampleCostData = ($estado['costos_ventas'] + $estado['gastos_operativos'] + $estado['impuestos']) <= 0;
        $useSampleProfitData = ($estado['ingresos'] + $gastosTotales + max($estado['utilidad_neta'], 0)) <= 0;
        $useSampleData = $useSampleCostData || $useSampleProfitData || $cxp->isEmpty() || $cxc->isEmpty() || $informes->isEmpty();

        $cxpDisplay = $cxp->isNotEmpty()
            ? $cxp
            : collect([
                (object)['descripcion' => 'Mantenimiento general', 'monto' => 12000, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-08')],
                (object)['descripcion' => 'Servicios de consultoría', 'monto' => 7500, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-12')],
                (object)['descripcion' => 'Pago de energía', 'monto' => 4200, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-14')],
            ]);

        $cxcDisplay = $cxc->isNotEmpty()
            ? $cxc
            : collect([
                (object)['descripcion' => 'Cliente: AutoExpress', 'monto' => 18500, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-09')],
                (object)['descripcion' => 'Cliente: MotorPlus', 'monto' => 9800, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-11')],
                (object)['descripcion' => 'Cliente: RutaSegura', 'monto' => 12600, 'created_at' => \Illuminate\Support\Carbon::parse('2026-05-15')],
            ]);

        $informesDisplay = $informes->isNotEmpty()
            ? $informes
            : collect([
                (object)['tipo' => 'Balance mensual', 'periodo' => 'Abr 2026', 'ruta' => 'samples/balance_abril.pdf', 'nombre_archivo' => 'Balance_Abril_2026.pdf'],
                (object)['tipo' => 'Informe de caja', 'periodo' => 'Abr 2026', 'ruta' => 'samples/caja_abril.pdf', 'nombre_archivo' => 'Informe_Caja_2026.pdf'],
                (object)['tipo' => 'Resumen de impuestos', 'periodo' => 'Abr 2026', 'ruta' => 'samples/impuestos_abril.pdf', 'nombre_archivo' => 'Resumen_Impuestos_2026.pdf'],
            ]);
    @endphp

    @if($useSampleData)
        <div class="flash-banner" style="padding:0 1rem; margin-bottom:1rem;">
            <div class="flash info" role="status" style="border-radius:18px; padding:1rem;">
                <i class="fas fa-info-circle"></i>
                <span>Se muestran datos de ejemplo en algunas secciones porque faltan valores reales en el periodo.</span>
            </div>
        </div>
    @endif

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
            @if($useSampleCostData)
                <p style="margin-top:0.75rem; color:#6b7280; font-size:0.95rem;">Mostrando datos de ejemplo porque no hay costos reales cargados aún.</p>
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
            @if($useSampleProfitData)
                <p style="margin-top:0.75rem; color:#6b7280; font-size:0.95rem;">Mostrando datos de ejemplo porque no existen ingresos y gastos reales en este periodo.</p>
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
                    @foreach($cxpDisplay as $t)
                        <tr>
                            <td>{{ $t->descripcion ?? '-' }}</td>
                            <td>${{ number_format($t->monto,2) }}</td>
                            <td>{{ $t->created_at instanceof \Illuminate\Support\Carbon ? $t->created_at->format('Y-m-d') : $t->created_at }}</td>
                        </tr>
                    @endforeach
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
                    @foreach($cxcDisplay as $t)
                        <tr>
                            <td>{{ $t->descripcion ?? '-' }}</td>
                            <td>${{ number_format($t->monto,2) }}</td>
                            <td>{{ $t->created_at instanceof \Illuminate\Support\Carbon ? $t->created_at->format('Y-m-d') : $t->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-panel" style="padding:1.25rem; margin-bottom:1rem;">
        <h3>Informes Financieros</h3>
        <div class="data-table-wrap" style="margin-top:0.75rem;">
            <table class="data-table">
                <thead>
                    <tr><th>Tipo</th><th>Periodo</th><th>Archivo</th></tr>
                </thead>
                <tbody>
                    @foreach($informesDisplay as $inf)
                        <tr>
                            <td>{{ $inf->tipo }}</td>
                            <td>{{ $inf->periodo }}</td>
                            <td><a href="{{ asset('storage/'.$inf->ruta) }}" target="_blank">{{ $inf->nombre_archivo }}</a></td>
                        </tr>
                    @endforeach
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
