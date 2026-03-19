<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->index();
            $table->uuid('branch_id')->nullable()->index();

            $table->string('sku', 100);
            $table->string('barcode', 100)->nullable();
            $table->string('barcode_type', 10)->nullable()
                  ->comment('EAN13, EAN8, UPC, QR, CODE128, GS1');

            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();

            $table->string('type', 20)
                  ->comment('physical, consumable, service, digital, bundle, composite, variant');
            $table->string('status', 20)->default('active')
                  ->comment('active, inactive, discontinued');

            $table->uuid('category_id')->nullable()->index();
            $table->uuid('base_uom_id')->nullable();
            $table->uuid('buying_uom_id')->nullable();
            $table->uuid('selling_uom_id')->nullable();

            $table->string('cost_method', 20)->default('weighted_average')
                  ->comment('fifo, lifo, weighted_average');

            $table->boolean('is_serialized')->default(false);
            $table->boolean('is_lot_tracked')->default(false);
            $table->boolean('is_batch_tracked')->default(false);
            $table->boolean('has_expiry')->default(false);

            $table->decimal('weight', 10, 4)->nullable();
            $table->string('weight_unit', 20)->nullable();
            $table->json('dimensions')->nullable();
            $table->json('images')->nullable();
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku'], 'products_tenant_sku_unique');
            $table->unique(['tenant_id', 'slug'], 'products_tenant_slug_unique');
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);

            $table->foreign('category_id')
                  ->references('id')
                  ->on('product_categories')
                  ->nullOnDelete();

            $table->foreign('base_uom_id')
                  ->references('id')
                  ->on('units_of_measure')
                  ->nullOnDelete();

            $table->foreign('buying_uom_id')
                  ->references('id')
                  ->on('units_of_measure')
                  ->nullOnDelete();

            $table->foreign('selling_uom_id')
                  ->references('id')
                  ->on('units_of_measure')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
