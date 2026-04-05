<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('sku');
            $table->string('barcode')->nullable();
            $table->string('type')->default('physical');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->string('unit')->default('each');
            $table->decimal('cost_price', 18, 4)->default(0);
            $table->decimal('selling_price', 18, 4)->default(0);
            $table->unsignedBigInteger('tax_group_id')->nullable();
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->decimal('weight', 10, 4)->nullable();
            $table->json('dimensions')->nullable();
            $table->json('images')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'sku']);
            $table->foreign('category_id')->references('id')->on('product_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
