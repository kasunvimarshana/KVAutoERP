<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_components', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('component_id');
            $table->unsignedBigInteger('component_variant_id')->nullable();
            $table->decimal('quantity', 20, 6);
            $table->string('unit_of_measure', 50)->default('unit');
            $table->string('notes', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'component_id', 'component_variant_id']);
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();
            $table->foreign('component_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();
            $table->foreign('component_variant_id')
                ->references('id')
                ->on('product_variants')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
