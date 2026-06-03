<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partido_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('partido_id')->constrained('partidos')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('jugador_id')->constrained('players')->cascadeOnDelete();
            $table->boolean('asistio_primera_mitad')->nullable();
            $table->boolean('asistio_segunda_mitad')->nullable();
            $table->timestamps();

            $table->unique(['partido_id', 'jugador_id']);
            $table->index(['partido_id', 'equipo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partido_asistencias');
    }
};
