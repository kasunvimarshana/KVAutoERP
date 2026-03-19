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
        Schema::create('uom_conversions', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('from_uom_id');
            $table->uuid('to_uom_id');
            // 6 decimal places for maximum precision in unit conversions.
            $table->decimal('factor', 20, 6);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'from_uom_id', 'to_uom_id'], 'uom_conversions_unique');
            $table->index(['tenant_id', 'from_uom_id']);
            $table->index(['tenant_id', 'to_uom_id']);

            $table->foreign('from_uom_id')
                  ->references('id')
                  ->on('units_of_measure')
                  ->cascadeOnDelete();

            $table->foreign('to_uom_id')
                  ->references('id')
                  ->on('units_of_measure')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};
