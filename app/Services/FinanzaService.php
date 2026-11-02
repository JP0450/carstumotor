<?php

namespace App\Services;

use App\Interfaces\IFinanzaRepository;
use App\Models\InformeFinanciero;
use App\Models\TransaccionFinanciera;
use App\Models\Vehiculo;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class FinanzaService
{
    public function __construct(
        private IFinanzaRepository $finanzaRepository,
        private FinanzasVehiculosService $finanzasVehiculos
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
        $data = $this->agregarDatosHistoricosDelVehiculo($data);

        if (
            $data['tipo'] === 'ingreso'
            && $data['categoria'] === 'Venta de vehículo'
            && ! empty($data['vehiculo_id'])
        ) {
            return $this->finanzasVehiculos->registrarVenta(
                Vehiculo::query()->findOrFail($data['vehiculo_id']),
                (float) $data['monto'],
                $data['fecha'],
                $data['user_id'] ?? null,
                $data['moneda']
            );
        }

        return $this->finanzaRepository->createTransaccion($data);
    }

    public function listarMovimientos(array $filters = []): LengthAwarePaginator
    {
        return $this->finanzaRepository->paginateTransacciones($filters);
    }

    public function obtenerConteosMovimientos(): array
    {
        return $this->finanzaRepository->countTransaccionesByType();
    }

    public function obtenerConteosMensualesMovimientos(int $cantidadMeses = 6): array
    {
        $ultimoMes = CarbonImmutable::today()->startOfMonth();
        $primerMes = $ultimoMes->subMonths($cantidadMeses - 1);
        $filas = $this->finanzaRepository->countTransaccionesByTypeAndDate(
            $primerMes->toDateString(),
            $ultimoMes->endOfMonth()->toDateString()
        );
        $nombresMeses = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $meses = [];

        for ($indice = 0; $indice < $cantidadMeses; $indice++) {
            $mes = $primerMes->addMonths($indice);
            $meses[$mes->format('Y-m')] = [
                'label' => $nombresMeses[(int) $mes->format('n')].' '.$mes->format('Y'),
                'ingresos' => 0,
                'egresos' => 0,
            ];
        }

        foreach ($filas as $fila) {
            $claveMes = CarbonImmutable::parse($fila->fecha)->format('Y-m');
            if (! isset($meses[$claveMes])) {
                continue;
            }

            if ($fila->tipo === 'ingreso') {
                $meses[$claveMes]['ingresos'] += (int) $fila->cantidad;
            } elseif (in_array($fila->tipo, ['gasto', 'impuesto', 'costo_venta'], true)) {
                $meses[$claveMes]['egresos'] += (int) $fila->cantidad;
            }
        }

        return [
            'labels' => array_column($meses, 'label'),
            'ingresos' => array_column($meses, 'ingresos'),
            'egresos' => array_column($meses, 'egresos'),
        ];
    }

    public function actualizarTransaccion(TransaccionFinanciera $transaccion, array $data): TransaccionFinanciera
    {
        if (
            in_array($transaccion->categoria, ['Compra de vehículo', 'Venta de vehículo'], true)
            && (
                $data['tipo'] !== $transaccion->tipo
                || $data['categoria'] !== $transaccion->categoria
                || (int) ($data['vehiculo_id'] ?? 0) !== (int) ($transaccion->vehiculo_id ?? 0)
            )
        ) {
            throw ValidationException::withMessages([
                'tipo' => 'No se puede cambiar el origen o el vehículo de una compra o venta registrada automáticamente.',
            ]);
        }

        $data = $this->agregarDatosHistoricosDelVehiculo($data);

        if (
            $data['tipo'] === 'ingreso'
            && $data['categoria'] === 'Venta de vehículo'
            && ! empty($data['vehiculo_id'])
        ) {
            return $this->finanzasVehiculos->registrarVenta(
                Vehiculo::query()->findOrFail($data['vehiculo_id']),
                (float) $data['monto'],
                $data['fecha'],
                $transaccion->user_id,
                $data['moneda'],
                $transaccion
            );
        }

        return $this->finanzaRepository->updateTransaccion($transaccion, $data);
    }

    public function eliminarTransaccion(TransaccionFinanciera $transaccion): bool
    {
        if (in_array($transaccion->categoria, ['Compra de vehículo', 'Venta de vehículo'], true)) {
            throw ValidationException::withMessages([
                'transaccion' => 'Las compras y ventas de vehículos no se pueden eliminar; corrige sus datos para conservar el historial.',
            ]);
        }

        return $this->finanzaRepository->deleteTransaccion($transaccion);
    }

    public function obtenerFlujoCaja(string $inicio, string $fin, ?string $moneda = null): array
    {
        $transacciones = $this->finanzaRepository->findTransaccionesByPeriodo($inicio, $fin);
        if ($moneda !== null) {
            $transacciones = $transacciones->where('moneda', $moneda);
        }

        return [
            'periodo' => ['inicio' => $inicio, 'fin' => $fin],
            ...$this->resumirFlujoPorMoneda($transacciones, $moneda),
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

    public function obtenerEstadoResultados(string $inicio, string $fin, ?string $moneda = null): array
    {
        $transacciones = $this->finanzaRepository->findTransaccionesByPeriodo($inicio, $fin);
        if ($moneda !== null) {
            $transacciones = $transacciones->where('moneda', $moneda);
        }

        return [
            ...$this->resumirEstadoResultadosPorMoneda($transacciones, $moneda),
        ];
    }

    private function resumirFlujoPorMoneda(Collection $transacciones, ?string $monedaSeleccionada): array
    {
        $porMoneda = $transacciones
            ->whereNotNull('moneda')
            ->groupBy('moneda')
            ->map(fn (Collection $movimientos) => $this->calcularFlujo($movimientos))
            ->all();
        $registrosSinMoneda = $transacciones->whereNull('moneda')->count();
        $monedaUnica = count($porMoneda) === 1 && $registrosSinMoneda === 0
            ? array_key_first($porMoneda)
            : null;
        $sinMovimientos = $transacciones->isEmpty();
        $totales = $monedaUnica !== null
            ? $porMoneda[$monedaUnica]
            : ($sinMovimientos ? ['entradas' => 0.0, 'salidas' => 0.0, 'flujo_neto' => 0.0] : [
                'entradas' => null,
                'salidas' => null,
                'flujo_neto' => null,
            ]);

        return [
            ...$totales,
            'moneda' => $monedaSeleccionada ?? $monedaUnica,
            'por_moneda' => $porMoneda,
            'registros_sin_moneda' => $registrosSinMoneda,
        ];
    }

    private function resumirEstadoResultadosPorMoneda(Collection $transacciones, ?string $monedaSeleccionada): array
    {
        $porMoneda = $transacciones
            ->whereNotNull('moneda')
            ->groupBy('moneda')
            ->map(fn (Collection $movimientos) => $this->calcularEstadoResultados($movimientos))
            ->all();
        $registrosSinMoneda = $transacciones->whereNull('moneda')->count();
        $monedaUnica = count($porMoneda) === 1 && $registrosSinMoneda === 0
            ? array_key_first($porMoneda)
            : null;
        $sinMovimientos = $transacciones->isEmpty();
        $totales = $monedaUnica !== null
            ? $porMoneda[$monedaUnica]
            : ($sinMovimientos ? $this->calcularEstadoResultados($transacciones) : [
                'ingresos' => null,
                'costos_ventas' => null,
                'utilidad_bruta' => null,
                'gastos_operativos' => null,
                'utilidad_operativa' => null,
                'impuestos' => null,
                'utilidad_neta' => null,
            ]);

        return [
            ...$totales,
            'moneda' => $monedaSeleccionada ?? $monedaUnica,
            'por_moneda' => $porMoneda,
            'registros_sin_moneda' => $registrosSinMoneda,
        ];
    }

    private function calcularFlujo(Collection $transacciones): array
    {
        $entradas = $transacciones->where('tipo', 'ingreso')->sum('monto');
        $salidas = $transacciones->whereIn('tipo', ['gasto', 'impuesto', 'costo_venta'])->sum('monto');

        return [
            'entradas' => round($entradas, 2),
            'salidas' => round($salidas, 2),
            'flujo_neto' => round($entradas - $salidas, 2),
        ];
    }

    private function calcularEstadoResultados(Collection $transacciones): array
    {
        $ingresos = $transacciones->where('tipo', 'ingreso')->sum('monto');
        $costos = $transacciones->where('tipo', 'costo_venta')->sum('monto');
        $gastos = $transacciones->where('tipo', 'gasto')->sum('monto');
        $impuestos = $transacciones->where('tipo', 'impuesto')->sum('monto');
        $utilidadBruta = $ingresos - $costos;
        $utilidadOperativa = $utilidadBruta - $gastos;

        return [
            'ingresos' => round($ingresos, 2),
            'costos_ventas' => round($costos, 2),
            'utilidad_bruta' => round($utilidadBruta, 2),
            'gastos_operativos' => round($gastos, 2),
            'utilidad_operativa' => round($utilidadOperativa, 2),
            'impuestos' => round($impuestos, 2),
            'utilidad_neta' => round($utilidadOperativa - $impuestos, 2),
        ];
    }

    private function agregarDatosHistoricosDelVehiculo(array $data): array
    {
        if (empty($data['vehiculo_id'])) {
            return $data;
        }

        $vehiculo = Vehiculo::query()->findOrFail($data['vehiculo_id']);

        return [
            ...$data,
            'vehiculo_referencia' => $vehiculo->id,
            'vehiculo_marca' => $vehiculo->marca,
        ];
    }
}
