<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_layers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('quantity', 14, 4);
            $table->decimal('original_quantity', 14, 4);
            $table->decimal('unit_cost', 18, 4);
            $table->string('method')->default('fifo');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'product_id', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_layers');
    }
};
