<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock reservations — hold quantity for specific orders or processes
 * without physically moving it, preventing over-selling.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_reservations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('warehouse_id')->index();
            $table->uuid('bin_id')->nullable()->index();
            $table->uuid('lot_id')->nullable()->index();

            $table->string('reference_type', 60)
                  ->comment('sales_order, work_order, transfer, etc.');
            $table->uuid('reference_id')->index();

            $table->decimal('qty_reserved', 18, 4);
            $table->decimal('qty_fulfilled', 18, 4)->default('0.0000');
            $table->decimal('qty_remaining', 18, 4)->default('0.0000')
                  ->comment('Remaining = reserved - fulfilled; updated by application');

            $table->string('status', 20)->default('active')
                  ->comment('active, partially_fulfilled, fulfilled, cancelled');
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'product_id', 'warehouse_id', 'status']);
            $table->index(['tenant_id', 'reference_type', 'reference_id']);

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
