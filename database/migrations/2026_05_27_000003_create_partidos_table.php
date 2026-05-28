<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->foreignId('jornada_id')->nullable()->constrained('jornadas')->nullOnDelete();
            $table->foreignId('equipo_local_id')->constrained('torneo_equipos')->cascadeOnDelete();
            $table->foreignId('equipo_visitante_id')->constrained('torneo_equipos')->cascadeOnDelete();
            $table->foreignId('cancha_id')->nullable()->constrained('canchas')->nullOnDelete();
            $table->foreignId('arbitro_id')->nullable()->constrained('arbitros')->nullOnDelete();

            $table->date('fecha');
            $table->time('hora');
            $table->unsignedSmallInteger('duracion_minutos')->default(90);

            $table->string('estado')->default('programado');

            $table->unsignedTinyInteger('goles_local')->nullable();
            $table->unsignedTinyInteger('goles_visitante')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'torneo_id']);
            $table->index(['cancha_id', 'fecha']);
            $table->index(['arbitro_id', 'fecha']);
            $table->index(['jornada_id']);
            $table->index(['fecha', 'estado']);
            $table->index(['torneo_id', 'equipo_local_id', 'equipo_visitante_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }
};
