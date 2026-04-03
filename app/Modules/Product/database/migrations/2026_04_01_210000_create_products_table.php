<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 15, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->string('type')->default('physical');
            $table->json('units_of_measure')->nullable();
            $table->json('product_attributes')->nullable();
            $table->json('attributes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
