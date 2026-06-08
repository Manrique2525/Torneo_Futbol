<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torneo_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->foreignId('torneo_grupo_id')->nullable()->constrained('torneo_grupos')->nullOnDelete();
            $table->foreignId('torneo_equipo_id')->constrained('torneo_equipos')->cascadeOnDelete();

            $table->unsignedSmallInteger('pj')->default(0);   // partidos jugados
            $table->unsignedSmallInteger('pg')->default(0);   // ganados
            $table->unsignedSmallInteger('pe')->default(0);   // empatados
            $table->unsignedSmallInteger('pp')->default(0);   // perdidos
            $table->unsignedSmallInteger('gf')->default(0);   // goles a favor
            $table->unsignedSmallInteger('gc')->default(0);   // goles en contra
            $table->smallInteger('dg')->default(0);           // diferencia de goles
            $table->unsignedSmallInteger('pts')->default(0);   // puntos
            $table->decimal('fair_play', 5, 2)->default(10.00);
            $table->unsignedSmallInteger('posicion')->nullable(); // orden calculado

            $table->timestamps();

            $table->unique(['torneo_id', 'torneo_equipo_id']);
            $table->index(['torneo_id', 'torneo_grupo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torneo_standings');
    }
};
