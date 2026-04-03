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
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name', 80);              // Basic, Pro, Enterprise
    $table->string('slug', 80)->unique();     // basic, pro, enterprise
    $table->text('description')->nullable();

    // Pricing
    $table->decimal('monthly_price', 10, 2);
    $table->decimal('annual_price', 10, 2);
    $table->string('currency', 3)->default('MXN');

    // Limits — use -1 for unlimited
    $table->integer('max_tournaments')->default(2);
    $table->integer('max_teams')->default(20);
    $table->integer('max_players')->default(300);
    $table->integer('max_users')->default(2);
    $table->integer('max_fields')->default(5);
    $table->integer('max_referees')->default(10);
    $table->integer('storage_mb')->default(500);

    // Feature flags
    $table->boolean('has_mobile_app')->default(false);
    $table->boolean('has_streaming')->default(false);
    $table->boolean('has_advanced_stats')->default(false);
    $table->boolean('has_api_access')->default(false);
    $table->boolean('has_whatsapp')->default(false);
    $table->boolean('has_reports')->default(false);
    $table->boolean('has_custom_domain')->default(false);

    // Support
    $table->enum('support_level', ['basic', 'priority', 'dedicated'])->default('basic');

    $table->unsignedSmallInteger('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false); // "Most popular" badge
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
