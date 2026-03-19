<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock items table — records the current on-hand quantity of a product
 * at a specific warehouse/bin combination. This is a derived aggregate
 * that is kept in sync by the ledger transaction processor.
 *
 * Pessimistic locking (SELECT FOR UPDATE) is used during stock deductions
 * to prevent race conditions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index()->comment('External ref; no FK across services');
            $table->uuid('warehouse_id')->index();
            $table->uuid('bin_id')->nullable()->index();
            $table->uuid('lot_id')->nullable()->index();

            $table->decimal('qty_on_hand', 18, 4)->default('0.0000');
            $table->decimal('qty_reserved', 18, 4)->default('0.0000');
            $table->decimal('qty_available', 18, 4)->default('0.0000')
                  ->comment('Available = on_hand - reserved; updated by application');

            $table->string('uom_id', 36)->nullable()->comment('Base UOM external ref');
            $table->decimal('unit_cost', 18, 4)->default('0.0000')
                  ->comment('Weighted average or last cost in tenant base currency');

            $table->integer('version')->default(1)->comment('Optimistic lock version');

            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'product_id', 'warehouse_id', 'bin_id', 'lot_id'],
                'stock_items_unique_location',
            );
            $table->index(['tenant_id', 'product_id', 'warehouse_id']);

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
