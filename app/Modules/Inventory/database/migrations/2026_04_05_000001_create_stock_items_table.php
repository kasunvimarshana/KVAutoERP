<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->decimal('quantity', 16, 4)->default(0);
            $table->decimal('reserved_quantity', 16, 4)->default(0);
            $table->decimal('available_quantity', 16, 4)->default(0);
            $table->string('unit', 20)->default('unit');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','product_id','variant_id','warehouse_id','location_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('stock_items'); }
};
