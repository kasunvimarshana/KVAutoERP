<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_layers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('batch_number', 100)->nullable();
            $table->string('lot_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('received_at');
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity_received', 20, 6);
            $table->decimal('quantity_remaining', 20, 6);
            $table->decimal('cost_per_unit', 20, 6);
            $table->string('valuation_method', 20);
            $table->boolean('is_exhausted')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('product_id');
            $table->index('is_exhausted');
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_layers');
    }
};
