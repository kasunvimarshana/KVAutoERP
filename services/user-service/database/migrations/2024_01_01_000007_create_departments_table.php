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
        Schema::create('departments', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->string('name', 255);
            $table->string('code', 100);
            $table->uuid('parent_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Code is unique per organisation.
            $table->unique(['organization_id', 'code']);

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('departments')
                  ->nullOnDelete();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
