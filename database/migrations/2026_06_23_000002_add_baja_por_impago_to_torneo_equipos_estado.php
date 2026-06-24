<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE torneo_equipos MODIFY COLUMN estado ENUM('pendiente','aprobado','rechazado','retirado','descalificado','baja_por_impago') DEFAULT 'pendiente'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE torneo_equipos MODIFY COLUMN estado ENUM('pendiente','aprobado','rechazado','retirado','descalificado') DEFAULT 'pendiente'");
        }
    }
};
