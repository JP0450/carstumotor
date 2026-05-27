<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'document')) {
                $table->string('document')->nullable()->unique()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'phone') || Schema::hasColumn('users', 'address') || Schema::hasColumn('users', 'document')) {
            Schema::table('users', function (Blueprint $table) {
                $columns = [];
                if (Schema::hasColumn('users', 'phone')) {
                    $columns[] = 'phone';
                }
                if (Schema::hasColumn('users', 'address')) {
                    $columns[] = 'address';
                }
                if (Schema::hasColumn('users', 'document')) {
                    $columns[] = 'document';
                }

                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
