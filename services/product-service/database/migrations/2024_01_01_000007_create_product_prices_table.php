<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_prices', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('product_id')->index();
            $table->char('currency_code', 3)->comment('ISO 4217 currency code');
            $table->string('price_type', 20)->comment('base, buying, selling, tier');
            // Tier minimum quantity — 6 decimal places for fractional quantities.
            $table->decimal('tier_min_qty', 20, 6)->nullable();
            // Price stored to 4 decimal places for financial precision (BCMath).
            $table->decimal('price', 20, 4);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->uuid('location_id')->nullable()->index();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'product_id', 'currency_code', 'price_type'], 'product_prices_lookup_index');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
