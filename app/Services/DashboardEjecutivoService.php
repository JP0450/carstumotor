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
        $ingresos = $this->finanzaRepository
            ->findTransaccionesByTipo('ingreso')
            ->sum('monto');

        $gastos = $this->finanzaRepository
            ->findTransaccionesByTipo('gasto')
            ->sum('monto');

        $utilidadesNetas = $ingresos - $gastos;

        $impuestos = $this->finanzaRepository
            ->findTransaccionesByTipo('impuesto')
            ->sum('monto');

        $porcentajeImpuestos = $ingresos > 0
            ? round(($impuestos / $ingresos) * 100, 2)
            : 0;

        $margenBruto = $ingresos > 0
            ? round((($ingresos - $this->calcularCostoVentas()) / $ingresos) * 100, 2)
            : 0;

        return [
            'utilidades_netas' => round($utilidadesNetas, 2),
            'impuestos_totales' => round($impuestos, 2),
            'porcentaje_impuestos' => $porcentajeImpuestos,
            'margen_bruto' => $margenBruto,
            'ingresos_totales' => round($ingresos, 2),
            'gastos_operativos' => round($gastos, 2),
        ];
    }

    private function calcularCostoVentas(): float
    {
        return $this->finanzaRepository
            ->findTransaccionesByTipo('costo_venta')
            ->sum('monto');
    }
}
