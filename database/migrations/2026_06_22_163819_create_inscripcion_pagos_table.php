<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscripcion_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('torneo_equipo_id')->constrained('torneo_equipos')->cascadeOnDelete();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 3)->default('MXN');
            $table->string('metodo_pago', 30)->nullable();
            $table->string('comprobante_path', 255)->nullable();
            $table->string('comprobante_original', 255)->nullable();
            $table->string('referencia', 100)->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->foreignId('confirmado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmado_at')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['torneo_id', 'estado']);
            $table->index(['team_id', 'torneo_id']);
            $table->index(['tenant_id', 'torneo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscripcion_pagos');
    }
};
