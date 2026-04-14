<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('torneos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('nombre');
            $table->string('tipo'); // liga, copa, relampago
            $table->string('categoria'); // libre, infantil, veteranos
            $table->string('rama'); // varonil, femenil, mixta
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->text('reglas')->nullable();
            $table->enum('estado', ['activo', 'finalizado', 'cancelado'])->default('activo');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('torneos');
    }
};
