<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('equipo_id');
            $table->string('nombre');
            $table->integer('numero')->nullable();
            $table->string('posicion')->nullable();
            $table->integer('edad')->nullable();
            $table->string('curp')->nullable()->unique();
            $table->string('foto')->nullable();
            $table->string('estado')->default('activo'); 
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('equipo_id')->references('id')->on('teams')->onDelete('cascade');
            $table->index(['tenant_id', 'equipo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};