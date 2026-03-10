<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary(); $table->string('tenant_id')->index(); $table->string('user_id')->index();
            $table->string('order_number')->unique();
            $table->enum('status',['pending','confirmed','shipped','delivered','cancelled','failed'])->default('pending')->index();
            $table->enum('saga_status',['started','completed','compensated','failed'])->default('started')->index();
            $table->decimal('subtotal',15,4)->default(0); $table->decimal('tax',15,4)->default(0); $table->decimal('discount',15,4)->default(0); $table->decimal('total',15,4)->default(0);
            $table->json('shipping_address')->nullable(); $table->text('notes')->nullable(); $table->json('metadata')->nullable();
            $table->timestamp('confirmed_at')->nullable(); $table->timestamp('cancelled_at')->nullable();
            $table->timestamps(); $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('orders'); }
};
