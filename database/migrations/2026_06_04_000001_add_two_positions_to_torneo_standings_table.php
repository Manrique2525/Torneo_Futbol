<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneo_standings', function (Blueprint $table) {
            $table->dropColumn('posicion');
        });

        Schema::table('torneo_standings', function (Blueprint $table) {
            $table->unsignedSmallInteger('posicion_posiciones')->nullable()->after('fair_play');
            $table->unsignedSmallInteger('posicion_rendimiento')->nullable()->after('posicion_posiciones');
        });
    }

    public function down(): void
    {
        Schema::table('torneo_standings', function (Blueprint $table) {
            $table->dropColumn('posicion_posiciones');
            $table->dropColumn('posicion_rendimiento');
        });

        Schema::table('torneo_standings', function (Blueprint $table) {
            $table->unsignedSmallInteger('posicion')->nullable()->after('fair_play');
        });
    }
};
