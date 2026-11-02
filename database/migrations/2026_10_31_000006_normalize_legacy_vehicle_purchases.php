<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transacciones_financieras')
            ->where('tipo', 'gasto')
            ->whereIn('categoria', ['compra_vehiculo', 'Compra de vehículo'])
            ->whereNotNull('vehiculo_id')
            ->orderBy('id')
            ->get(['id', 'vehiculo_id', 'monto', 'moneda'])
            ->each(function (object $movimiento): void {
                $vehiculo = DB::table('vehiculos')->where('id', $movimiento->vehiculo_id)->first(['id', 'marca']);
                if ($vehiculo === null) {
                    return;
                }

                DB::table('transacciones_financieras')
                    ->where('vehiculo_id', $vehiculo->id)
                    ->where('tipo', 'gasto')
                    ->where('categoria', 'Compra de vehículo')
                    ->where('descripcion', 'like', "Compra histórica registrada para vehículo #{$vehiculo->id}:%")
                    ->where('monto', $movimiento->monto)
                    ->delete();

                DB::table('transacciones_financieras')
                    ->where('id', $movimiento->id)
                    ->update([
                        'categoria' => 'Compra de vehículo',
                        'moneda' => $movimiento->moneda ?? 'COP',
                        'vehiculo_referencia' => $vehiculo->id,
                        'vehiculo_marca' => $vehiculo->marca,
                    ]);
            });
    }

    public function down(): void
    {
        // Keep normalized financial history intact when rolling back application code.
    }
};
