<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->time('hora_inicio')->nullable()->default('12:00')->after('playoff_ida_vuelta');
            $table->unsignedSmallInteger('duracion_minutos')->nullable()->default(90)->after('hora_inicio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'duracion_minutos']);
        });
    }
};
