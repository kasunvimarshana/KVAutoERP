<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('original_order_id')->nullable();
            $table->string('type')->default('sales_return');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->text('reason')->nullable();
            $table->decimal('restocking_fee', 18, 4)->default(0);
            $table->decimal('credit_memo_amount', 18, 4)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('quality_check')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('original_order_id')->references('id')->on('orders')->nullOnDelete();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
