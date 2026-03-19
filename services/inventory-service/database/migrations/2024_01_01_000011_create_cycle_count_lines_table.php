<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cycle count lines — one line per product/bin being counted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycle_count_lines', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('cycle_count_id')->index();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('bin_id')->nullable()->index();
            $table->uuid('lot_id')->nullable()->index();

            $table->decimal('system_qty', 18, 4)
                  ->comment('Quantity per ledger at the time of count');
            $table->decimal('counted_qty', 18, 4)->nullable()
                  ->comment('Physically counted quantity (null = not yet counted)');
            $table->decimal('variance_qty', 18, 4)->nullable()
                  ->comment('Variance = counted - system; updated by application');

            $table->decimal('unit_cost', 18, 4)->default('0.0000');
            $table->string('uom_id', 36)->nullable();
            $table->string('count_status', 20)->default('pending')
                  ->comment('pending, counted, approved, adjusted');
            $table->text('notes')->nullable();

            $table->uuid('counted_by')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->timestamps();

            $table->foreign('cycle_count_id')
                  ->references('id')
                  ->on('cycle_counts')
                  ->cascadeOnDelete();

            $table->index(['cycle_count_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_count_lines');
    }
};
