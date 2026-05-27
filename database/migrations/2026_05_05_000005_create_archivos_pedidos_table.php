<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archivos_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_internacional_id')->constrained('pedidos_internacionales')->onDelete('cascade');
            $table->string('nombre_original');
            $table->string('ruta');
            $table->string('tipo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archivos_pedidos');
    }
};
