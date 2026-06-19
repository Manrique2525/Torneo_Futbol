<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->string('fase')->default('regular')->after('estado');
            $table->boolean('es_vuelta')->default(false)->after('fase');
            $table->string('llave_bracket')->nullable()->after('es_vuelta');
            $table->unsignedTinyInteger('orden_bracket')->nullable()->after('llave_bracket');

            $table->index(['torneo_id', 'fase']);
            $table->index(['torneo_id', 'llave_bracket']);
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $table->dropIndex(['torneo_id', 'fase']);
            $table->dropIndex(['torneo_id', 'llave_bracket']);
            $table->dropColumn(['fase', 'es_vuelta', 'llave_bracket', 'orden_bracket']);
        });
    }
};
