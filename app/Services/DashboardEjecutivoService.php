<?php

namespace App\Services;

use App\Interfaces\IFinanzaRepository;
use App\Interfaces\IVehiculoRepository;

class DashboardEjecutivoService
{
    public function __construct(
        private IFinanzaRepository $finanzaRepository,
        private IVehiculoRepository $vehiculoRepository
    ) {}

    public function obtenerKPIs(): array
    {
        $transaccionesPorTipo = [
            'ingreso' => $this->finanzaRepository->findTransaccionesByTipo('ingreso'),
            'gasto' => $this->finanzaRepository->findTransaccionesByTipo('gasto'),
            'costo_venta' => $this->finanzaRepository->findTransaccionesByTipo('costo_venta'),
            'impuesto' => $this->finanzaRepository->findTransaccionesByTipo('impuesto'),
        ];
        $transacciones = collect($transaccionesPorTipo)->flatten(1);
        $monedas = $transacciones->pluck('moneda')->filter()->unique()->values();
        $monedaIncierta = $transacciones->contains(fn ($transaccion) => ! $transaccion->moneda);

        if ($monedaIncierta || $monedas->count() > 1) {
            return [
                'utilidades_netas' => null,
                'impuestos_totales' => null,
                'porcentaje_impuestos' => null,
                'margen_bruto' => null,
                'ingresos_totales' => null,
                'gastos_operativos' => null,
                'moneda' => null,
            ];
        }

        $ingresos = $transaccionesPorTipo['ingreso']->sum('monto');
        $gastos = $transaccionesPorTipo['gasto']->sum('monto');
        $costosVentas = $transaccionesPorTipo['costo_venta']->sum('monto');
        $impuestos = $transaccionesPorTipo['impuesto']->sum('monto');

        $utilidadesNetas = $ingresos - $costosVentas - $gastos - $impuestos;

        $porcentajeImpuestos = $ingresos > 0
            ? round(($impuestos / $ingresos) * 100, 2)
            : 0;

        $margenBruto = $ingresos > 0
            ? round((($ingresos - $costosVentas) / $ingresos) * 100, 2)
            : 0;

        return [
            'utilidades_netas' => round($utilidadesNetas, 2),
            'impuestos_totales' => round($impuestos, 2),
            'porcentaje_impuestos' => $porcentajeImpuestos,
            'margen_bruto' => $margenBruto,
            'ingresos_totales' => round($ingresos, 2),
            'gastos_operativos' => round($gastos, 2),
            'moneda' => $monedas->first(),
        ];
    }
}
