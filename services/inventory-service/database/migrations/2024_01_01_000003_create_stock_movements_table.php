<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id', 100);
            $table->uuid('product_id');
            $table->enum('type', ['in', 'out', 'adjustment', 'reservation', 'release', 'damage', 'return']);
            $table->integer('quantity');
            $table->string('reference', 255)->nullable();
            $table->string('reason', 500)->nullable();
            $table->integer('previous_quantity')->default(0);
            $table->integer('new_quantity')->default(0);
            $table->string('performed_by', 255)->default('system');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['product_id', 'created_at']);
            $table->index(['tenant_id', 'reference']);
            $table->index(['tenant_id', 'type']);
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
