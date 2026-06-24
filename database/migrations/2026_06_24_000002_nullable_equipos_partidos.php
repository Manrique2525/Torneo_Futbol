<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->foreignId('equipo_local_id')->nullable()->change();
            $table->foreignId('equipo_visitante_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->foreignId('equipo_local_id')->nullable(false)->change();
            $table->foreignId('equipo_visitante_id')->nullable(false)->change();
        });
    }
};
