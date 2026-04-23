<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('serials', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->string('serial_number');
            $table->enum('status', ['available', 'reserved', 'sold', 'returned', 'defective', 'scrapped'])->default('available');
            $table->timestamp('sold_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('set null');
            $table->unique(['tenant_id', 'serial_number'], 'serials_tenant_id_serial_number_uk');
            $table->index(['tenant_id', 'product_id', 'status'], 'serials_tenant_product_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serials');
    }
};
