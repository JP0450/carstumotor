<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones_financieras', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->decimal('monto', 15, 2);
            $table->text('descripcion');
            $table->date('fecha');
            $table->string('categoria');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones_financieras');
    }
};
