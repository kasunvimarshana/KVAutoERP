<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('order_number', 50);
            $table->string('type')->default('sale');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->char('currency', 3)->default('USD');
            $table->decimal('subtotal', 18, 4)->default(0);
            $table->decimal('discount_amount', 18, 4)->default(0);
            $table->decimal('tax_amount', 18, 4)->default(0);
            $table->decimal('shipping_amount', 18, 4)->default(0);
            $table->decimal('total_amount', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
