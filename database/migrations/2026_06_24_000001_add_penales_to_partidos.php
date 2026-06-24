<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->tinyInteger('goles_penales_local')->nullable()->after('goles_visitante');
            $table->tinyInteger('goles_penales_visitante')->nullable()->after('goles_penales_local');
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->dropColumn(['goles_penales_local', 'goles_penales_visitante']);
        });
    }
};
