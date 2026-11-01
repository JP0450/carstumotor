<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculo_informacion_internacional', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehiculo_id')->unique()->constrained('vehiculos')->cascadeOnDelete();
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('version')->nullable();
            $table->string('vin', 30)->nullable();
            $table->string('numero_motor')->nullable();
            $table->string('tipo_vehiculo')->nullable();
            $table->unsignedInteger('kilometraje')->nullable();
            $table->string('combustible')->nullable();
            $table->string('estado')->nullable();
            $table->string('proveedor_internacional')->nullable();
            $table->string('pais_compra')->nullable();
            $table->decimal('precio_adquisicion', 15, 2)->nullable();
            $table->string('moneda', 10)->nullable();
            $table->date('fecha_compra')->nullable();
            $table->string('incoterm', 20)->nullable();
            $table->string('pais_origen')->nullable();
            $table->string('pais_procedencia')->nullable();
            $table->string('pais_destino')->nullable();
            $table->string('puerto_salida')->nullable();
            $table->string('puerto_llegada')->nullable();
            $table->string('transportadora')->nullable();
            $table->string('agencia_aduanas')->nullable();
            $table->date('fecha_embarque')->nullable();
            $table->date('fecha_llegada')->nullable();
            $table->string('numero_declaracion_importacion')->nullable();
            $table->decimal('valor_fob', 15, 2)->nullable();
            $table->decimal('flete_internacional', 15, 2)->nullable();
            $table->decimal('seguro', 15, 2)->nullable();
            $table->decimal('valor_cif', 15, 2)->nullable();
            $table->decimal('aranceles', 15, 2)->nullable();
            $table->decimal('impuestos', 15, 2)->nullable();
            $table->decimal('gastos_aduaneros', 15, 2)->nullable();
            $table->decimal('costo_total_importacion', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculo_informacion_internacional');
    }
};
