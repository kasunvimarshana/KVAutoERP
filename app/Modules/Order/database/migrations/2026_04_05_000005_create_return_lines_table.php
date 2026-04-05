<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('return_id');
            $table->unsignedBigInteger('order_line_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('quantity', 14, 4);
            $table->string('condition')->default('good');
            $table->decimal('unit_price', 18, 4);
            $table->unsignedBigInteger('restock_to_warehouse_id')->nullable();
            $table->unsignedBigInteger('restock_to_location_id')->nullable();
            $table->boolean('should_restock')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('return_id')->references('id')->on('returns')->cascadeOnDelete();
            $table->index('return_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_lines');
    }
};
