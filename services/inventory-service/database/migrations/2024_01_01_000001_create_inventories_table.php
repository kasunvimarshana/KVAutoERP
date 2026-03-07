<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            // Primary key – use UUID for global uniqueness across tenants
            $table->uuid('id')->primary();

            // Multi-tenancy column
            $table->string('tenant_id', 36)->index();

            // Core fields
            $table->string('sku', 100);
            $table->string('name', 255);
            $table->text('description')->nullable();

            // Stock quantities
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);

            // Pricing
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('unit_price', 12, 2)->default(0.00);

            // Classification / location
            $table->string('category', 100)->nullable()->index();
            $table->string('location', 255)->nullable();

            // Stock level thresholds
            $table->unsignedInteger('min_stock_level')->default(0);
            $table->unsignedInteger('max_stock_level')->default(9999);

            // Status
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active')->index();

            // Flexible metadata (e.g. supplier info, dimensions, tags)
            $table->json('metadata')->nullable();

            // Timestamps + soft deletes
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['tenant_id', 'sku'], 'inventories_tenant_sku_unique');
            $table->index(['tenant_id', 'status'],   'inventories_tenant_status_idx');
            $table->index(['tenant_id', 'category'], 'inventories_tenant_category_idx');
            $table->index('quantity',                 'inventories_quantity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
