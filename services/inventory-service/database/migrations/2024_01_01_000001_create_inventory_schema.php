<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory Service Schema Migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // Categories
        // =====================================================================
        Schema::create('categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'slug']);
        });

        // =====================================================================
        // Warehouses
        // =====================================================================
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'code']);
        });

        // =====================================================================
        // Inventory Items
        // =====================================================================
        Schema::create('inventory_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->uuid('category_id')->nullable();
            $table->uuid('warehouse_id')->nullable();
            $table->string('sku')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('reorder_point')->default(0);
            $table->integer('reorder_quantity')->default(1);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('unit_price', 12, 4)->nullable();
            $table->string('unit_of_measure', 50)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'category_id']);
        });

        // =====================================================================
        // Stock Movements (Event Sourcing / Audit Trail)
        // =====================================================================
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->foreignUuid('inventory_item_id')->constrained('inventory_items');
            $table->string('type', 30);  // in|out|adjustment|reservation|release|transfer
            $table->integer('quantity');
            $table->integer('before_quantity');
            $table->integer('after_quantity');
            $table->string('reference_type', 50)->nullable(); // order|purchase|transfer
            $table->string('reference_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->string('performed_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'inventory_item_id', 'created_at']);
        });

        // =====================================================================
        // Webhook Endpoints
        // =====================================================================
        Schema::create('webhook_endpoints', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('url');
            $table->json('events');       // ['*'] or ['inventory.created', ...]
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('categories');
    }
};
