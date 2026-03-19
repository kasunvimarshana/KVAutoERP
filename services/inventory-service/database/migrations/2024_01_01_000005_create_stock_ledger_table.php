<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock ledger table — the immutable, append-only record of every stock
 * movement that has ever occurred. Records are NEVER updated or deleted.
 * Historical stock levels can be reconstructed by replaying the ledger.
 *
 * Each row represents a signed quantity delta on a specific product at
 * a specific warehouse/bin, optionally tied to a lot/serial.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledger', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->uuid('bin_id')->nullable()->index();
            $table->uuid('lot_id')->nullable()->index();

            $table->string('transaction_type', 40)
                  ->comment('receive, dispatch, adjustment, transfer_in, transfer_out, reservation, reservation_release, cycle_count, opening_balance, write_off, return_in, return_out, scrap');
            $table->string('reference_type', 60)->nullable()
                  ->comment('purchase_order, sales_order, stock_transfer, adjustment, cycle_count, etc.');
            $table->uuid('reference_id')->nullable()->index()
                  ->comment('UUID of the source document (PO id, SO id, transfer id, etc.)');
            $table->string('idempotency_key', 100)->nullable()->unique()
                  ->comment('Client-provided key to prevent duplicate processing');

            // Signed delta: positive = inflow, negative = outflow.
            $table->decimal('qty_change', 18, 4)
                  ->comment('Signed quantity change in base UOM');
            // Snapshot of on-hand after this transaction.
            $table->decimal('qty_after', 18, 4)
                  ->comment('On-hand quantity at this location after applying qty_change');

            $table->decimal('unit_cost', 18, 4)->default('0.0000');
            $table->decimal('total_cost', 18, 4)->default('0.0000');
            $table->string('currency', 3)->default('USD');

            $table->string('uom_id', 36)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->uuid('performed_by')->nullable()
                  ->comment('User who triggered the movement (from JWT claims)');
            $table->timestamp('transacted_at')->useCurrent()
                  ->comment('Business timestamp of the movement');
            $table->timestamps();

            $table->index(['tenant_id', 'product_id', 'transacted_at']);
            $table->index(['tenant_id', 'warehouse_id', 'transacted_at']);
            $table->index(['tenant_id', 'transaction_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
