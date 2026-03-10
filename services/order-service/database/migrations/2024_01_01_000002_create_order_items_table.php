<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->uuid('order_id')->index(); $table->string('product_id')->index();
            $table->string('product_code'); $table->string('product_name');
            $table->integer('quantity')->default(1); $table->decimal('unit_price',15,4)->default(0); $table->decimal('discount',15,4)->default(0); $table->decimal('total',15,4)->default(0);
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }
    public function down(): void { Schema::dropIfExists('order_items'); }
};
