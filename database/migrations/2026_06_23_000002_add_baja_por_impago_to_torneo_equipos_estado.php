<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE torneo_equipos MODIFY COLUMN estado ENUM('pendiente','aprobado','rechazado','retirado','descalificado','baja_por_impago') DEFAULT 'pendiente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE torneo_equipos MODIFY COLUMN estado ENUM('pendiente','aprobado','rechazado','retirado','descalificado') DEFAULT 'pendiente'");
    }
};
