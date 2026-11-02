<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->foreignId('responsable_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('tipo')->default('general')->after('vehiculo_id');
            $table->string('nombre_contacto')->nullable()->after('tipo');
            $table->string('telefono_contacto', 30)->nullable()->after('nombre_contacto');
            $table->index(['tipo', 'estado', 'responsable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex(['tipo', 'estado', 'responsable_id']);
            $table->dropConstrainedForeignId('responsable_id');
            $table->dropColumn(['tipo', 'nombre_contacto', 'telefono_contacto']);
        });
    }
};
