<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('sku', 100);
            $table->string('name');
            $table->string('type', 20)->default('physical');
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->decimal('cost_price', 20, 4)->default(0);
            $table->decimal('sale_price', 20, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_taxable')->default(false);
            $table->unsignedBigInteger('tax_group_id')->nullable();
            $table->string('barcode', 100)->nullable()->index();
            $table->string('unit', 20)->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','sku']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
