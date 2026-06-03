<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partido_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('partido_id')->constrained('partidos')->cascadeOnDelete();
            $table->foreignId('equipo_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('jugador_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('jugador_relacionado_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('tipo'); // gol, autogol, gol_penal, tarjeta_amarilla, tarjeta_roja, falta, sustitucion_entrada, sustitucion_salida, penal_concedido
            $table->unsignedTinyInteger('minuto');
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->index(['partido_id', 'tipo']);
            $table->index(['partido_id', 'minuto']);
            $table->index(['jugador_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partido_eventos');
    }
};
