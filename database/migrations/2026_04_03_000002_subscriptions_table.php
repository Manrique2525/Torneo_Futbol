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
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants');
    $table->foreignId('plan_id')->constrained('plans');

    $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled'])->default('trial');
    $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');

    $table->decimal('price_paid', 10, 2);       // actual price at moment of subscription
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->string('discount_reason', 150)->nullable();

    $table->date('starts_at');
    $table->date('ends_at')->nullable();
    $table->date('trial_ends_at')->nullable();
    $table->date('next_billing_at')->nullable();

    $table->boolean('auto_renew')->default(true);
    $table->string('payment_method', 30)->nullable(); // stripe, paypal, transfer
    $table->string('external_id', 100)->nullable();   // Stripe subscription ID, etc.

    $table->timestamp('cancelled_at')->nullable();
    $table->string('cancellation_reason', 255)->nullable();

    $table->timestamps();

    $table->index(['tenant_id', 'status'], 'idx_subscriptions_tenant_status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
