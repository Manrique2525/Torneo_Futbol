<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partido_sustituciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partido_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipo_original_id')->constrained('torneo_equipos')->cascadeOnDelete();
            $table->foreignId('equipo_sustituto_id')->constrained('torneo_equipos')->cascadeOnDelete();
            $table->string('motivo');
            $table->string('tipo_resolucion');
            $table->foreignId('partido_reprogramado_id')->nullable()->constrained('partidos')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'partido_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partido_sustituciones');
    }
};
