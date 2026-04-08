<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->string('slug', 100)->unique();
            $table->string('custom_domain', 255)->nullable()->unique();
            $table->string('logo')->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('country', 5)->default('MX');
            $table->string('timezone', 50)->default('America/Mexico_City');
            $table->string('locale', 5)->default('es');
            $table->string('currency', 3)->default('MXN');
            $table->string('plan', 20)->default('basic');
            $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
            $table->timestamp('last_activity_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
