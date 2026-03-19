<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_bins', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('warehouse_id')->index();

            $table->string('code', 50);
            $table->string('name', 255)->nullable();
            $table->string('zone', 50)->nullable()->comment('Storage zone (A, B, C, cold, etc.)');
            $table->string('aisle', 50)->nullable();
            $table->string('rack', 50)->nullable();
            $table->string('shelf', 50)->nullable();
            $table->string('position', 50)->nullable();
            $table->string('type', 30)->default('standard')
                  ->comment('standard, receiving, dispatch, quarantine, damaged');
            $table->string('status', 20)->default('active');
            $table->decimal('capacity', 14, 4)->nullable()->comment('Maximum capacity in base UOM');
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'code'], 'bins_warehouse_code_unique');
            $table->index(['tenant_id', 'warehouse_id']);

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_bins');
    }
};
