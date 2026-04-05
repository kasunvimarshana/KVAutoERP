<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('inventory_adjustments')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->decimal('expected_quantity', 14, 4)->default(0);
            $table->decimal('actual_quantity', 14, 4)->default(0);
            $table->decimal('variance', 14, 4)->default(0);
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('unit_cost', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('adjustment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_lines');
    }
};
