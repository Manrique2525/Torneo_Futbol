<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canchas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('direccion')->nullable();
            $table->string('tipo')->default('futbol-11');
            $table->integer('capacidad')->nullable()->default(0);
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();

            $table->index(['tenant_id', 'nombre']);
            $table->index(['tenant_id', 'tipo']);
            $table->index(['tenant_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canchas');
    }
};
