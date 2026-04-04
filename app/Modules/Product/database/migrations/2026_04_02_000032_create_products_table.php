<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable();
            $table->string('type')->default('physical');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('base_uom_id')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_trackable')->default(true);
            $table->boolean('is_serialized')->default(false);
            $table->boolean('is_batch_tracked')->default(false);
            $table->boolean('has_expiry')->default(false);
            $table->decimal('weight', 10, 3)->nullable();
            $table->string('weight_unit')->nullable();
            $table->decimal('length', 10, 3)->nullable();
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->string('dimension_unit')->nullable();
            $table->text('attributes')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'type']);
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
