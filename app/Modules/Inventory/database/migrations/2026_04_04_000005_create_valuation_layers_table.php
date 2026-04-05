<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('valuation_layers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('batch_lot_id')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('remaining_quantity', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->string('valuation_method', 20)->default('fifo');
            $table->datetime('received_at');
            $table->string('reference', 200)->nullable();
            $table->index(['tenant_id', 'product_id', 'location_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_layers');
    }
};
