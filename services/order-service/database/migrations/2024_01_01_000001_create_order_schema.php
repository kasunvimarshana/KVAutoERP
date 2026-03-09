<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Order Service Schema Migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // Orders
        // =====================================================================
        Schema::create('orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('customer_id')->index();
            $table->string('status', 30)->default('pending')->index();
            $table->string('saga_status', 30)->default('pending')->index();
            $table->string('saga_transaction_id')->nullable()->index();
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('tax_amount', 12, 4)->default(0);
            $table->decimal('total_amount', 12, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status', 'created_at']);
            $table->index(['tenant_id', 'customer_id']);
        });

        // =====================================================================
        // Order Items
        // =====================================================================
        Schema::create('order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('inventory_item_id');
            $table->string('sku');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 4);
            $table->decimal('total_price', 12, 4);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
        });

        // =====================================================================
        // Saga Logs (Distributed Transaction Audit Trail)
        // =====================================================================
        Schema::create('saga_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('saga_transaction_id')->index();
            $table->string('order_id')->nullable()->index();
            $table->string('step_name', 100);
            $table->string('action', 20);      // execute | compensate
            $table->string('status', 20);      // started | completed | failed
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['saga_transaction_id', 'step_name']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_logs');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
