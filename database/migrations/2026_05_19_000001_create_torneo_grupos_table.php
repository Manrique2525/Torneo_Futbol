<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torneo_grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('torneo_id')->constrained('torneos')->cascadeOnDelete();
            $table->string('nombre', 50);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['torneo_id', 'nombre']);
            $table->index(['tenant_id', 'torneo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torneo_grupos');
    }
};
