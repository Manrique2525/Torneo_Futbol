<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torneo_equipos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('torneo_grupo_id')->nullable()->constrained('torneo_grupos')->nullOnDelete();

            $table->unsignedSmallInteger('seed')->nullable();
            $table->enum('estado', [
                'pendiente',
                'aprobado',
                'rechazado',
                'retirado',
                'descalificado',
            ])->default('pendiente');

            $table->decimal('fair_play_points', 5, 2)->default(10);

            $table->timestamp('inscrito_at')->useCurrent();
            $table->timestamp('aprobado_at')->nullable();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rechazado_at')->nullable();
            $table->string('motivo_rechazo', 255)->nullable();
            $table->timestamp('retirado_at')->nullable();
            $table->text('notas')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['torneo_id', 'team_id']);
            $table->unique(['torneo_id', 'seed']);
            $table->index(['torneo_id', 'estado']);
            $table->index(['tenant_id', 'torneo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torneo_equipos');
    }
};
