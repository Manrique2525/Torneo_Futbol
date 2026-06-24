<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->string('tipo_gestion', 10)->nullable()->after('pago_requerido');
        });

        DB::table('torneos')->whereNull('tipo_gestion')->update(['tipo_gestion' => 'manual']);
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn('tipo_gestion');
        });
    }
};
