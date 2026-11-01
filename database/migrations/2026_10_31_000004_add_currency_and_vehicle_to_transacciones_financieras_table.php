<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transacciones_financieras', function (Blueprint $table) {
            $table->string('moneda', 3)->nullable()->after('monto');
            $table->foreignId('vehiculo_id')
                ->nullable()
                ->after('moneda')
                ->constrained('vehiculos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transacciones_financieras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehiculo_id');
            $table->dropColumn('moneda');
        });
    }
};
