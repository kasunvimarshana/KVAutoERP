<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 15, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->json('attribute_values')->nullable();
            $table->string('status')->default('active');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('product_variations'); }
};
