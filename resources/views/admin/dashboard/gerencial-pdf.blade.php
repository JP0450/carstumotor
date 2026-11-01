<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe gerencial</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color:#172033; font-size:11px; }
        h1 { margin:0 0 6px; font-size:20px; }
        p { color:#64748b; }
        table { width:100%; border-collapse:collapse; margin:14px 0; }
        th, td { padding:8px; border:1px solid #dbe2ea; text-align:left; }
        th { background:#f1f5f9; }
        .warning { padding:9px; background:#fffbeb; border:1px solid #fde68a; }
    </style>
</head>
<body>
    <h1>Informe gerencial</h1>
    <p>Periodo: {{ $dashboard['periodo']['etiqueta'] }} ({{ $dashboard['periodo']['inicio'] }} a {{ $dashboard['periodo']['fin'] }})</p>
    <p class="warning">Resumen de consulta en {{ $dashboard['periodo']['moneda'] }}. No se convierten divisas. La utilidad es ingresos menos egresos registrados y no sustituye un cierre contable. Los importes de cuentas por cobrar/pagar son registros brutos, no saldos pendientes. No incluye datos personales.</p>
    <table>
        <thead><tr><th>Indicador</th><th>Valor</th><th>Observación</th></tr></thead>
        <tbody>
            @foreach ($dashboard['kpis'] as $clave => $kpi)
                <tr>
                    <td>{{ str_replace('_', ' ', ucfirst($clave)) }}</td>
                    <td>{{ in_array($clave, ['ingresos', 'utilidad_neta', 'cuentas_por_cobrar', 'cuentas_por_pagar'], true) ? $dashboard['periodo']['moneda'].' ' : '' }}{{ number_format((float) ($kpi['valor'] ?? 0), 2, ',', '.') }}{{ $clave === 'margen_promedio' ? '%' : '' }}</td>
                    <td>{{ $kpi['nota'] }}</td>
                </tr>
            @endforeach
            <tr><td>Movimientos de ingreso</td><td>{{ $dashboard['finanzas']['registrosIngresos'] }}</td><td>Importes en {{ $dashboard['periodo']['moneda'] }}.</td></tr>
            <tr><td>Movimientos de egreso</td><td>{{ $dashboard['finanzas']['registrosEgresos'] }}</td><td>Importes en {{ $dashboard['periodo']['moneda'] }}.</td></tr>
            <tr><td>Pedidos de importación en curso</td><td>{{ $dashboard['pedidos']['enCurso'] }}</td><td>Los pedidos no están vinculados a vehículos.</td></tr>
            <tr><td>Pedidos de importación retrasados</td><td>{{ $dashboard['pedidos']['retrasados'] }}</td><td>Pedidos no entregados con fecha estimada vencida.</td></tr>
        </tbody>
    </table>
</body>
</html>
