<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_internacionales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('codigo')->unique();
            $table->string('descripcion');
            $table->string('proveedor');
            $table->decimal('valor', 15, 2);
            $table->unsignedTinyInteger('progreso')->default(0);
            $table->string('estado')->default('en_espera');
            $table->date('fecha_pedido');
            $table->date('fecha_estimada_llegada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_internacionales');
    }
};
