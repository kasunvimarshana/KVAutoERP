<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Multi-tenancy
            $table->string('tenant_id')->index();

            // Order identification (unique per tenant, same sequence allowed across tenants)
            $table->string('order_number');
            $table->string('saga_id')->nullable()->index();

            // Customer
            $table->string('customer_id')->index();
            $table->string('customer_name');
            $table->string('customer_email');

            // Line items (JSON array of product/quantity/price objects)
            $table->json('items');

            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax',      12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total',    12, 2)->default(0);

            // Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'completed',
                'cancelled',
                'failed',
            ])->default('pending')->index();

            // Payment
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
            ])->default('pending')->index();

            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();

            // Addresses
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();

            // Extra
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for common query patterns
            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
