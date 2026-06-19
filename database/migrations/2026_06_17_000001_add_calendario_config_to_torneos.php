<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->boolean('ida_y_vuelta')->default(false)->after('fair_play_automatico');
            $table->string('formato_relampago')->nullable()->after('ida_y_vuelta');
            $table->boolean('tiene_playoff')->default(false)->after('formato_relampago');
            $table->unsignedSmallInteger('playoff_equipos')->nullable()->after('tiene_playoff');
            $table->boolean('playoff_ida_vuelta')->default(false)->after('playoff_equipos');
        });
    }

    public function down(): void
    {
        Schema::table('torneos', function (Blueprint $table) {
            $table->dropColumn([
                'ida_y_vuelta',
                'formato_relampago',
                'tiene_playoff',
                'playoff_equipos',
                'playoff_ida_vuelta',
            ]);
        });
    }
};
