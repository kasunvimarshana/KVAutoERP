<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stock transfers — move stock between warehouses or bins within the same tenant.
 * Each transfer has a header record and one or more line items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->index();

            $table->string('transfer_number', 50)->nullable();
            $table->string('status', 20)->default('draft')
                  ->comment('draft, in_transit, completed, cancelled');
            $table->string('transfer_type', 30)->default('internal')
                  ->comment('internal, cross_branch, drop_ship');

            $table->uuid('from_warehouse_id')->index();
            $table->uuid('from_bin_id')->nullable()->index();
            $table->uuid('to_warehouse_id')->index();
            $table->uuid('to_bin_id')->nullable()->index();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'transfer_number'], 'transfers_tenant_number_unique');
            $table->index(['tenant_id', 'status']);

            $table->foreign('from_warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->restrictOnDelete();

            $table->foreign('to_warehouse_id')
                  ->references('id')
                  ->on('warehouses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
