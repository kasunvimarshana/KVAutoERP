<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('batch_lots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('batch_number', 100);
            $table->string('lot_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->decimal('quantity', 15, 4);
            $table->decimal('remaining_quantity', 15, 4);
            $table->unsignedBigInteger('location_id');
            $table->string('status', 20)->default('active');
            $table->json('metadata')->nullable();
            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'batch_number']);
            $table->foreign('location_id')->references('id')->on('stock_locations');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_lots');
    }
};
