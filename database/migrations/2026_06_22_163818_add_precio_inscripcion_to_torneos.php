<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->decimal('precio_inscripcion', 10, 2)->nullable()->after('inscripcion_abierta');
            $table->string('moneda', 3)->default('MXN')->after('precio_inscripcion');
            $table->boolean('pago_requerido')->default(false)->after('moneda');
        });
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn(['precio_inscripcion', 'moneda', 'pago_requerido']);
        });
    }
};
