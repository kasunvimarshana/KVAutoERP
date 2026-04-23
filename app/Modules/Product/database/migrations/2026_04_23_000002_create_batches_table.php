<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('batch_number');
            $table->string('lot_number')->nullable();
            $table->date('manufactured_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 20, 6)->default(0);
            $table->enum('status', ['active', 'quarantine', 'expired', 'consumed'])->default('active');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
            $table->unique(['tenant_id', 'product_id', 'batch_number'], 'batches_tenant_product_batch_uk');
            $table->index(['tenant_id', 'expiry_date'], 'batches_tenant_id_expiry_date_idx');
            $table->index(['tenant_id', 'product_id', 'status'], 'batches_tenant_product_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
