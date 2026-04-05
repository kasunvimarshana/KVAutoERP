<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name', 255);
            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->string('type', 30);
            $table->string('status', 30)->default('active');
            $table->text('description')->nullable();
            $table->string('short_description', 500)->nullable();
            $table->string('unit_of_measure', 50)->default('unit');
            $table->decimal('weight', 20, 6)->nullable();
            $table->json('dimensions')->nullable();
            $table->json('images')->nullable();
            $table->json('attributes')->nullable();
            $table->string('tax_class', 50)->nullable();
            $table->decimal('cost_price', 20, 6)->default(0);
            $table->decimal('selling_price', 20, 6)->default(0);
            $table->boolean('is_serialized')->default(false);
            $table->boolean('track_inventory')->default(true);
            $table->decimal('min_stock_level', 20, 6)->default(0);
            $table->decimal('max_stock_level', 20, 6)->nullable();
            $table->decimal('reorder_point', 20, 6)->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku']);
            $table->foreign('category_id')
                ->references('id')
                ->on('product_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
