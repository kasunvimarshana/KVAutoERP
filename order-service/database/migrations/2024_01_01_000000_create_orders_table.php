<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('product_id');           // MongoDB ObjectId (string)
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price',  10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('status')->default('pending');
            $table->string('saga_state')->nullable();
            $table->string('payment_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
