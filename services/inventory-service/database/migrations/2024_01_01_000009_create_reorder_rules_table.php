<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reorder rules — define when and how much stock to reorder for a
 * product at a specific warehouse, supporting automated procurement suggestions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reorder_rules', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('warehouse_id')->index();

            $table->decimal('reorder_point', 18, 4)
                  ->comment('Quantity at which to trigger a reorder');
            $table->decimal('reorder_qty', 18, 4)
                  ->comment('Quantity to order when reorder point is reached');
            $table->decimal('max_qty', 18, 4)->nullable()
                  ->comment('Maximum stock target (for max-min replenishment)');
            $table->decimal('safety_stock', 18, 4)->default('0.0000');

            $table->string('uom_id', 36)->nullable();
            $table->integer('lead_time_days')->default(0)
                  ->comment('Expected supplier lead time in days');

            $table->boolean('is_active')->default(true);
            $table->uuid('preferred_supplier_id')->nullable()
                  ->comment('External ref to procurement service supplier');

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'product_id', 'warehouse_id'],
                'reorder_rules_unique',
            );

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reorder_rules');
    }
};
