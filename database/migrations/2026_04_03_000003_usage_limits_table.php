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
        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants');

            $table->unsignedInteger('active_tournaments')->default(0);
            $table->unsignedInteger('total_teams')->default(0);
            $table->unsignedInteger('total_players')->default(0);
            $table->unsignedInteger('total_users')->default(0);
            $table->unsignedInteger('storage_used_mb')->default(0);
            $table->unsignedInteger('api_requests_month')->default(0);
            $table->date('last_reset_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_limits');
    }
};
