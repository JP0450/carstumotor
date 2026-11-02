<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PURCHASE_CATEGORY = 'Compra de vehículo';

    public function up(): void
    {
        Schema::table('transacciones_financieras', function (Blueprint $table) {
            $table->unsignedBigInteger('vehiculo_referencia')->nullable()->after('vehiculo_id');
            $table->string('vehiculo_marca')->nullable()->after('vehiculo_referencia');
        });

        DB::table('transacciones_financieras')
            ->where('tipo', 'gasto')
            ->whereIn('categoria', ['compra_vehiculo', self::PURCHASE_CATEGORY])
            ->whereNotNull('vehiculo_id')
            ->orderBy('id')
            ->get(['id', 'vehiculo_id', 'moneda'])
            ->each(function (object $movimiento): void {
                $vehiculo = DB::table('vehiculos')->where('id', $movimiento->vehiculo_id)->first(['id', 'marca']);
                if ($vehiculo === null) {
                    return;
                }

                DB::table('transacciones_financieras')
                    ->where('id', $movimiento->id)
                    ->update([
                        'categoria' => self::PURCHASE_CATEGORY,
                        'moneda' => $movimiento->moneda ?? 'COP',
                        'vehiculo_referencia' => $vehiculo->id,
                        'vehiculo_marca' => $vehiculo->marca,
                    ]);
            });

        DB::table('transacciones_financieras')
            ->whereNotNull('vehiculo_id')
            ->orderBy('id')
            ->get(['id', 'vehiculo_id'])
            ->each(function (object $movimiento): void {
                $vehiculo = DB::table('vehiculos')->where('id', $movimiento->vehiculo_id)->first(['id', 'marca']);
                if ($vehiculo === null) {
                    return;
                }

                DB::table('transacciones_financieras')
                    ->where('id', $movimiento->id)
                    ->update([
                        'vehiculo_referencia' => $vehiculo->id,
                        'vehiculo_marca' => $vehiculo->marca,
                    ]);
            });

        DB::table('vehiculos')
            ->where('costo_empresa', '>', 0)
            ->orderBy('id')
            ->get(['id', 'marca', 'modelo', 'costo_empresa', 'created_at'])
            ->each(function (object $vehiculo): void {
                $yaRegistrado = DB::table('transacciones_financieras')
                    ->where('vehiculo_id', $vehiculo->id)
                    ->whereIn('tipo', ['gasto', 'costo_venta'])
                    ->where(function ($query): void {
                        $query->where('tipo', 'costo_venta')
                            ->orWhere('categoria', self::PURCHASE_CATEGORY);
                    })
                    ->exists();

                if ($yaRegistrado) {
                    return;
                }

                DB::table('transacciones_financieras')->insert([
                    'tipo' => 'gasto',
                    'monto' => $vehiculo->costo_empresa,
                    'moneda' => 'COP',
                    'vehiculo_id' => $vehiculo->id,
                    'vehiculo_referencia' => $vehiculo->id,
                    'vehiculo_marca' => $vehiculo->marca,
                    'descripcion' => "Compra histórica registrada para vehículo #{$vehiculo->id}: {$vehiculo->marca} {$vehiculo->modelo}",
                    'fecha' => $vehiculo->created_at
                        ? CarbonImmutable::parse($vehiculo->created_at)->toDateString()
                        : CarbonImmutable::today()->toDateString(),
                    'categoria' => self::PURCHASE_CATEGORY,
                    'user_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('transacciones_financieras')
            ->where('categoria', self::PURCHASE_CATEGORY)
            ->where('descripcion', 'like', 'Compra histórica registrada para vehículo #%')
            ->delete();

        Schema::table('transacciones_financieras', function (Blueprint $table) {
            $table->dropColumn(['vehiculo_referencia', 'vehiculo_marca']);
        });
    }
};
