<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_equipos')->nullable()->after('estado');
            $table->boolean('inscripcion_abierta')->default(true)->after('max_equipos');
        });
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn(['max_equipos', 'inscripcion_abierta']);
        });
    }
};
