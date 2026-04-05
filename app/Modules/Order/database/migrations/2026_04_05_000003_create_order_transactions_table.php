<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('order_id');
            $table->string('type');
            $table->decimal('amount', 18, 4);
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('pending');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_transactions');
    }
};
