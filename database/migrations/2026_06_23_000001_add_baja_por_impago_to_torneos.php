<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->boolean('baja_por_impago_automatica')->default(false)->after('pago_requerido');
            $table->unsignedTinyInteger('max_jornadas_sin_pago')->nullable()->after('baja_por_impago_automatica');
        });
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn(['baja_por_impago_automatica', 'max_jornadas_sin_pago']);
        });
    }
};
