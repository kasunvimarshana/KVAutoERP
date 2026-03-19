<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cycle count headers — physical inventory count sessions for a warehouse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycle_counts', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('warehouse_id')->index();

            $table->string('count_number', 50)->nullable();
            $table->string('status', 20)->default('draft')
                  ->comment('draft, in_progress, pending_approval, confirmed, cancelled');
            $table->string('count_type', 30)->default('full')
                  ->comment('full, partial, abc_class, zone, random');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->text('notes')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('confirmed_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'count_number'], 'cycle_counts_tenant_number_unique');
            $table->index(['tenant_id', 'status']);

            $table->foreign('warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_counts');
    }
};
