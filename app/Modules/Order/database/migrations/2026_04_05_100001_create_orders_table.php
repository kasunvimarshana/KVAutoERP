<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('order_number', 100)->index();
            $table->string('type', 20)->index();
            $table->string('status', 30)->default('draft')->index();
            $table->unsignedBigInteger('party_id')->index();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->date('order_date')->index();
            $table->string('currency', 3)->default('USD');
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('total_amount', 20, 4)->default(0);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','order_number']);
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
