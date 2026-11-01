<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\PedidoInternacional;
use App\Models\TransaccionFinanciera;
use App\Models\Vehiculo;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class DashboardGerencialService
{
    public const PERIODS = [
        '7_dias' => 'Últimos 7 días',
        '30_dias' => 'Últimos 30 días',
        'mes_actual' => 'Mes actual',
        '3_meses' => 'Últimos 3 meses',
        '6_meses' => 'Últimos 6 meses',
        'anio_actual' => 'Año actual',
        'personalizado' => 'Rango personalizado',
    ];

    private const EXPENSE_TYPES = ['gasto', 'impuesto', 'costo_venta'];

    public function obtenerDashboard(
        string $periodo,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        string $moneda = 'COP'
    ): array {
        [$inicio, $fin] = $this->resolverPeriodo($periodo, $fechaInicio, $fechaFin);
        $transaccionesDelPeriodo = $this->transaccionesAgregadas($inicio, $fin);
        $transaccionesMoneda = $transaccionesDelPeriodo->where('moneda', $moneda)->values();
        $serieFinanciera = $this->crearSerieMensual($inicio, $fin, $transaccionesMoneda);
        $serieVentas = $this->crearSerieVentas($inicio, $fin, $moneda);
        $registrosIngresos = (int) $transaccionesDelPeriodo->where('tipo', 'ingreso')->sum('cantidad');
        $registrosEgresos = (int) $transaccionesDelPeriodo->whereIn('tipo', self::EXPENSE_TYPES)->sum('cantidad');
        $ingresos = (float) $transaccionesMoneda->where('tipo', 'ingreso')->sum('total');
        $egresos = (float) $transaccionesMoneda->whereIn('tipo', self::EXPENSE_TYPES)->sum('total');
        $ventasPorVehiculo = $this->obtenerVentasPorVehiculo($inicio, $fin, $moneda);
        $ventas = count($ventasPorVehiculo['vehiculos']);
        $rentabilidadMarcas = $this->obtenerRentabilidadPorMarca($ventasPorVehiculo['montos']);
        $margenPromedio = $this->obtenerMargenPromedioCatalogo();
        $cuentasPorCobrar = $this->obtenerTotalTipoMovimiento('cuenta_por_cobrar', $moneda);
        $cuentasPorPagar = $this->obtenerTotalTipoMovimiento('cuenta_por_pagar', $moneda);

        $inventario = $this->obtenerResumenInventario();
        $pedidosEnCurso = PedidoInternacional::query()
            ->whereIn('estado', ['en_espera', 'en_transito'])
            ->count();

        $pedidosRetrasados = PedidoInternacional::query()
            ->whereNotNull('fecha_estimada_llegada')
            ->whereDate('fecha_estimada_llegada', '<', now()->toDateString())
            ->where('estado', '!=', 'entregado')
            ->count();

        $stockBajo = Inventario::query()
            ->where('cantidad', '<=', 3)
            ->count();

        return [
            'periodo' => [
                'clave' => $periodo,
                'etiqueta' => self::PERIODS[$periodo],
                'moneda' => $moneda,
                'inicio' => $inicio->toDateString(),
                'fin' => $fin->toDateString(),
                'esPersonalizado' => $periodo === 'personalizado',
            ],
            'kpis' => [
                'ingresos' => [
                    'valor' => round($ingresos, 2),
                    'registros' => $registrosIngresos,
                    'nota' => "{$moneda} · Solo se incluyen movimientos con esa moneda; los registros históricos sin moneda quedan excluidos.",
                ],
                'utilidad_neta' => [
                    'valor' => round($ingresos - $egresos, 2),
                    'nota' => "Resultado de ingresos menos egresos registrados en {$moneda}; no sustituye un cierre contable.",
                ],
                'inventario_disponible' => [
                    'valor' => $inventario['disponibles'],
                    'nota' => 'Unidades en registros de inventario asociados a vehículos disponibles.',
                ],
                'vehiculos_vendidos' => [
                    'valor' => $ventas,
                    'nota' => 'Vehículos asociados a movimientos de ingreso categorizados como venta de vehículo en el periodo.',
                ],
                'importaciones_en_curso' => [
                    'valor' => $pedidosEnCurso,
                    'nota' => 'Pedidos en curso; los pedidos no están vinculados a vehículos.',
                ],
                'cuentas_por_cobrar' => [
                    'valor' => round($cuentasPorCobrar, 2),
                    'nota' => "Importe bruto de registros clasificados como cuenta por cobrar en {$moneda}; no representa saldo pendiente.",
                ],
                'cuentas_por_pagar' => [
                    'valor' => round($cuentasPorPagar, 2),
                    'nota' => "Importe bruto de registros clasificados como cuenta por pagar en {$moneda}; no representa saldo pendiente.",
                ],
                'margen_promedio' => [
                    'valor' => $margenPromedio,
                    'nota' => 'Estimación promedio del catálogo: (precio de venta menos costo de empresa) / precio de venta; no representa ventas realizadas.',
                ],
            ],
            'finanzas' => [
                'ingresos' => round($ingresos, 2),
                'egresos' => round($egresos, 2),
                'registrosIngresos' => $registrosIngresos,
                'registrosEgresos' => $registrosEgresos,
                'moneda' => $moneda,
                'monedaDisponible' => true,
                'serie' => $serieFinanciera,
                'ventas' => $serieVentas,
                'rentabilidadMarcas' => $rentabilidadMarcas,
            ],
            'inventario' => $inventario,
            'catalogo' => [
                'disponibles' => Vehiculo::query()->whereRaw('disponible IS TRUE')->count(),
                'noDisponibles' => Vehiculo::query()->whereRaw('disponible IS FALSE')->count(),
            ],
            'pedidos' => [
                'enCurso' => $pedidosEnCurso,
                'retrasados' => $pedidosRetrasados,
            ],
            'alertas' => [
                'stockBajo' => $stockBajo,
                'importacionesRetrasadas' => $pedidosRetrasados,
            ],
            'graficas' => [
                'ventasDisponible' => true,
                'marcasDisponible' => true,
                'estadosInventarioDisponibles' => true,
            ],
        ];
    }

    public function obtenerOpcionesPeriodo(): array
    {
        return self::PERIODS;
    }

    private function resolverPeriodo(string $periodo, ?string $fechaInicio, ?string $fechaFin): array
    {
        $hoy = CarbonImmutable::today();
        $fin = $hoy;

        $inicio = match ($periodo) {
            '7_dias' => $hoy->subDays(6),
            '30_dias' => $hoy->subDays(29),
            '3_meses' => $hoy->startOfMonth()->subMonths(2),
            '6_meses' => $hoy->startOfMonth()->subMonths(5),
            'anio_actual' => $hoy->startOfYear(),
            'personalizado' => CarbonImmutable::parse($fechaInicio),
            default => $hoy->startOfMonth(),
        };

        if ($periodo === 'personalizado') {
            $fin = CarbonImmutable::parse($fechaFin);
        }

        return [$inicio->startOfDay(), $fin->endOfDay()];
    }

    private function transaccionesAgregadas(CarbonImmutable $inicio, CarbonImmutable $fin): Collection
    {
        return TransaccionFinanciera::query()
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->select('tipo', 'fecha', 'moneda')
            ->selectRaw('SUM(monto) AS total, COUNT(*) AS cantidad')
            ->groupBy('tipo', 'fecha', 'moneda')
            ->orderBy('fecha')
            ->get();
    }

    private function crearSerieMensual(CarbonImmutable $inicio, CarbonImmutable $fin, Collection $transacciones): array
    {
        $mes = $inicio->startOfMonth();
        $ultimoMes = $fin->startOfMonth();
        $labels = [];
        $ingresos = [];
        $egresos = [];

        while ($mes <= $ultimoMes) {
            $mesSiguiente = $mes->addMonth();
            $filasDelMes = $transacciones->filter(function ($transaccion) use ($mes, $mesSiguiente) {
                $fecha = CarbonImmutable::parse($transaccion->fecha);

                return $fecha >= $mes && $fecha < $mesSiguiente;
            });

            $labels[] = $mes->format('m/Y');
            $ingresos[] = (float) $filasDelMes->where('tipo', 'ingreso')->sum('total');
            $egresos[] = (float) $filasDelMes->whereIn('tipo', self::EXPENSE_TYPES)->sum('total');
            $mes = $mesSiguiente;
        }

        return compact('labels', 'ingresos', 'egresos');
    }

    private function crearSerieVentas(CarbonImmutable $inicio, CarbonImmutable $fin, string $moneda): array
    {
        $ventas = TransaccionFinanciera::query()
            ->where('tipo', 'ingreso')
            ->where('categoria', 'Venta de vehículo')
            ->whereNotNull('vehiculo_id')
            ->where('moneda', $moneda)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->get(['vehiculo_id', 'fecha'])
            ->groupBy(fn (TransaccionFinanciera $venta): string => CarbonImmutable::parse($venta->fecha)->format('Y-m'))
            ->map(fn (Collection $ventasMes): int => $ventasMes->pluck('vehiculo_id')->unique()->count());

        $labels = [];
        $cantidades = [];
        $mes = $inicio->startOfMonth();
        $ultimoMes = $fin->startOfMonth();

        while ($mes <= $ultimoMes) {
            $clave = $mes->format('Y-m');
            $labels[] = $mes->format('m/Y');
            $cantidades[] = (int) ($ventas[$clave] ?? 0);
            $mes = $mes->addMonth();
        }

        return compact('labels', 'cantidades');
    }

    private function obtenerVentasPorVehiculo(CarbonImmutable $inicio, CarbonImmutable $fin, string $moneda): array
    {
        $ventas = TransaccionFinanciera::query()
            ->where('tipo', 'ingreso')
            ->where('categoria', 'Venta de vehículo')
            ->whereNotNull('vehiculo_id')
            ->where('moneda', $moneda)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('vehiculo_id, SUM(monto) AS total')
            ->groupBy('vehiculo_id')
            ->get()
            ->keyBy('vehiculo_id');
        $costos = TransaccionFinanciera::query()
            ->where('tipo', 'costo_venta')
            ->whereNotNull('vehiculo_id')
            ->where('moneda', $moneda)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('vehiculo_id, SUM(monto) AS total')
            ->groupBy('vehiculo_id')
            ->pluck('total', 'vehiculo_id');
        $vehiculos = Vehiculo::query()
            ->whereIn('id', $ventas->keys())
            ->get(['id', 'marca']);
        $montos = [];

        foreach ($vehiculos as $vehiculo) {
            $montos[$vehiculo->id] = [
                'marca' => $vehiculo->marca,
                'ingresos' => (float) $ventas[$vehiculo->id]->total,
                'costos' => (float) ($costos[$vehiculo->id] ?? 0),
            ];
        }

        return [
            'vehiculos' => $ventas->keys()->all(),
            'montos' => $montos,
        ];
    }

    private function obtenerRentabilidadPorMarca(array $ventasPorVehiculo): array
    {
        $marcas = collect($ventasPorVehiculo)
            ->groupBy('marca')
            ->map(fn ($vehiculos) => [
                'ingresos' => (float) $vehiculos->sum('ingresos'),
                'utilidad' => (float) $vehiculos->sum(fn (array $vehiculo) => $vehiculo['ingresos'] - $vehiculo['costos']),
            ]);

        return [
            'labels' => $marcas->keys()->values()->all(),
            'ingresos' => $marcas->pluck('ingresos')->values()->all(),
            'utilidad' => $marcas->pluck('utilidad')->values()->all(),
        ];
    }

    private function obtenerMargenPromedioCatalogo(): float
    {
        $vehiculos = Vehiculo::query()
            ->where('precio_cliente', '>', 0)
            ->get(['precio_cliente', 'costo_empresa']);

        if ($vehiculos->isEmpty()) {
            return 0.0;
        }

        return round($vehiculos->avg(
            fn (Vehiculo $vehiculo): float => ((float) $vehiculo->precio_cliente - (float) $vehiculo->costo_empresa)
                / (float) $vehiculo->precio_cliente * 100
        ), 2);
    }

    private function obtenerTotalTipoMovimiento(string $tipo, string $moneda): float
    {
        return (float) TransaccionFinanciera::query()
            ->where('tipo', $tipo)
            ->where('moneda', $moneda)
            ->sum('monto');
    }

    private function obtenerResumenInventario(): array
    {
        $resumen = Inventario::query()
            ->join('vehiculos', 'vehiculos.id', '=', 'inventarios.vehiculo_id')
            ->selectRaw('COUNT(*) AS registros')
            ->selectRaw('COALESCE(SUM(CASE WHEN vehiculos.disponible IS TRUE THEN inventarios.cantidad ELSE 0 END), 0) AS disponibles')
            ->selectRaw('COALESCE(SUM(CASE WHEN vehiculos.disponible IS FALSE THEN inventarios.cantidad ELSE 0 END), 0) AS noDisponibles')
            ->first();

        return [
            'registros' => (int) ($resumen->registros ?? 0),
            'disponibles' => (int) ($resumen->disponibles ?? 0),
            'noDisponibles' => (int) ($resumen->noDisponibles ?? 0),
            'etiquetaNoDisponibles' => 'No disponibles en catálogo',
        ];
    }
}
