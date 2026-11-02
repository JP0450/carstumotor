<?php

namespace App\Services;

use App\Models\TransaccionFinanciera;
use App\Models\Vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinanzasVehiculosService
{
    public function registrarCompra(Vehiculo $vehiculo, ?int $userId): TransaccionFinanciera
    {
        return TransaccionFinanciera::create([
            'tipo' => 'gasto',
            'monto' => $vehiculo->costo_empresa,
            'moneda' => 'COP',
            'vehiculo_id' => $vehiculo->id,
            'vehiculo_referencia' => $vehiculo->id,
            'vehiculo_marca' => $vehiculo->marca,
            'descripcion' => "Compra de vehículo: {$vehiculo->marca} {$vehiculo->modelo}",
            'fecha' => now()->toDateString(),
            'categoria' => 'Compra de vehículo',
            'user_id' => $userId,
        ]);
    }

    public function registrarVenta(
        Vehiculo $vehiculo,
        float $monto,
        string $fecha,
        ?int $userId,
        string $moneda = 'COP',
        ?TransaccionFinanciera $movimientoExistente = null
    ): TransaccionFinanciera {
        return DB::transaction(function () use ($vehiculo, $monto, $fecha, $userId, $moneda, $movimientoExistente): TransaccionFinanciera {
            $vehiculo = Vehiculo::query()->lockForUpdate()->findOrFail($vehiculo->id);
            $ventasDelVehiculo = $vehiculo->transaccionesFinancieras()
                ->where('tipo', 'ingreso')
                ->where('categoria', 'Venta de vehículo');
            if ($movimientoExistente !== null) {
                $ventasDelVehiculo->whereKeyNot($movimientoExistente->id);
            }

            if ($ventasDelVehiculo->exists()) {
                throw ValidationException::withMessages([
                    'vehiculo' => 'Este vehículo ya tiene una venta financiera registrada.',
                ]);
            }

            $esVentaExistente = $movimientoExistente !== null
                && $movimientoExistente->tipo === 'ingreso'
                && $movimientoExistente->categoria === 'Venta de vehículo'
                && (int) $movimientoExistente->vehiculo_id === $vehiculo->id;
            $inventario = $esVentaExistente
                ? null
                : $vehiculo->inventario()->lockForUpdate()->first();

            if ($inventario !== null && $inventario->cantidad < 1) {
                throw ValidationException::withMessages([
                    'vehiculo' => 'No hay unidades disponibles en el inventario para registrar esta venta.',
                ]);
            }

            $datosVenta = [
                'tipo' => 'ingreso',
                'monto' => $monto,
                'moneda' => $moneda,
                'vehiculo_id' => $vehiculo->id,
                'vehiculo_referencia' => $vehiculo->id,
                'vehiculo_marca' => $vehiculo->marca,
                'descripcion' => "Venta de vehículo: {$vehiculo->marca} {$vehiculo->modelo}",
                'fecha' => $fecha,
                'categoria' => 'Venta de vehículo',
                'user_id' => $userId,
            ];
            if ($movimientoExistente !== null) {
                $movimientoExistente->update($datosVenta);
                $venta = $movimientoExistente->refresh();
            } else {
                $venta = TransaccionFinanciera::create($datosVenta);
            }

            $inventario?->decrement('cantidad');
            $vehiculo->update(['disponible' => false]);

            return $venta;
        });
    }
}
