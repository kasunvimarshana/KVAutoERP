<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_reservations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('location_id');
            $table->decimal('quantity', 15, 4);
            $table->string('reference_type', 50);
            $table->unsignedBigInteger('reference_id');
            $table->timestamp('expires_at')->nullable();
            $table->string('status', 20)->default('active');
            $table->index(['tenant_id', 'product_id', 'location_id', 'status']);
            $table->foreign('location_id')->references('id')->on('stock_locations');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
