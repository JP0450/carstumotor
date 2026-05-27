<?php

namespace App\Services;

use App\Interfaces\IFinanzaRepository;
use App\Models\InformeFinanciero;
use App\Models\TransaccionFinanciera;
use Illuminate\Database\Eloquent\Collection;

class FinanzaService
{
    public function __construct(
        private IFinanzaRepository $finanzaRepository
    ) {}

    public function subirInforme(array $data): InformeFinanciero
    {
        return $this->finanzaRepository->createInforme($data);
    }

    public function listarInformes(): Collection
    {
        return $this->finanzaRepository->allInformes();
    }

    public function registrarTransaccion(array $data): TransaccionFinanciera
    {
        return $this->finanzaRepository->createTransaccion($data);
    }

    public function obtenerFlujoCaja(string $inicio, string $fin): array
    {
        $transacciones = $this->finanzaRepository->findTransaccionesByPeriodo($inicio, $fin);

        $entradas = $transacciones->where('tipo', 'ingreso')->sum('monto');
        $salidas = $transacciones->whereIn('tipo', ['gasto', 'impuesto', 'costo_venta'])->sum('monto');

        return [
            'periodo' => ['inicio' => $inicio, 'fin' => $fin],
            'entradas' => round($entradas, 2),
            'salidas' => round($salidas, 2),
            'flujo_neto' => round($entradas - $salidas, 2),
        ];
    }

    public function obtenerCuentasPorPagar(): Collection
    {
        return $this->finanzaRepository
            ->findTransaccionesByTipo('cuenta_por_pagar');
    }

    public function obtenerCuentasPorCobrar(): Collection
    {
        return $this->finanzaRepository
            ->findTransaccionesByTipo('cuenta_por_cobrar');
    }

    public function obtenerEstadoResultados(string $inicio, string $fin): array
    {
        $transacciones = $this->finanzaRepository->findTransaccionesByPeriodo($inicio, $fin);

        $ingresos = $transacciones->where('tipo', 'ingreso')->sum('monto');
        $costos = $transacciones->where('tipo', 'costo_venta')->sum('monto');
        $gastos = $transacciones->where('tipo', 'gasto')->sum('monto');
        $impuestos = $transacciones->where('tipo', 'impuesto')->sum('monto');

        $utilidadBruta = $ingresos - $costos;
        $utilidadOperativa = $utilidadBruta - $gastos;
        $utilidadNeta = $utilidadOperativa - $impuestos;

        return [
            'ingresos' => round($ingresos, 2),
            'costos_ventas' => round($costos, 2),
            'utilidad_bruta' => round($utilidadBruta, 2),
            'gastos_operativos' => round($gastos, 2),
            'utilidad_operativa' => round($utilidadOperativa, 2),
            'impuestos' => round($impuestos, 2),
            'utilidad_neta' => round($utilidadNeta, 2),
        ];
    }
}
