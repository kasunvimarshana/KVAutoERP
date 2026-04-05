<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('price_type', 20)->default('fixed');
            $table->decimal('price', 15, 4);
            $table->decimal('min_quantity', 10, 4)->default(1);
            $table->decimal('max_quantity', 10, 4)->nullable();
            $table->text('notes')->nullable();
            $table->foreign('price_list_id')
                ->references('id')
                ->on('price_lists')
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};
