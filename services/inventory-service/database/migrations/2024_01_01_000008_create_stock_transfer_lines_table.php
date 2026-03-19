<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfer_lines', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('transfer_id')->index();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('lot_id')->nullable()->index();

            $table->decimal('qty_requested', 18, 4);
            $table->decimal('qty_dispatched', 18, 4)->default('0.0000');
            $table->decimal('qty_received', 18, 4)->default('0.0000');

            $table->string('uom_id', 36)->nullable();
            $table->decimal('unit_cost', 18, 4)->default('0.0000');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('transfer_id')
                  ->references('id')
                  ->on('stock_transfers')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_lines');
    }
};
