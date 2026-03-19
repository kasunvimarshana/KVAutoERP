<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock lots table — tracks serial numbers, lot numbers, and batch identifiers
 * with full expiry/quarantine lifecycle management.
 * Supports pharmaceutical compliance mode (FEFO enforcement).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_lots', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index()->comment('External ref; no FK across services');

            $table->string('lot_type', 20)->default('lot')
                  ->comment('serial, lot, batch');
            $table->string('lot_number', 100);
            $table->string('serial_number', 100)->nullable();
            $table->string('batch_number', 100)->nullable();

            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->date('best_before_date')->nullable();

            $table->string('status', 20)->default('available')
                  ->comment('available, reserved, quarantined, expired, consumed, recalled');

            $table->string('supplier_lot', 100)->nullable();
            $table->string('origin_country', 3)->nullable();
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'product_id', 'lot_number']);
            $table->index(['tenant_id', 'expiry_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_lots');
    }
};
