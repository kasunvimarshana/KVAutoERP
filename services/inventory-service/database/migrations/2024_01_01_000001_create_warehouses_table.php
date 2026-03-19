<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->index();
            $table->uuid('branch_id')->nullable()->index();

            $table->string('code', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('type', 30)->default('standard')
                  ->comment('standard, bonded, transit, virtual, consignment');
            $table->string('status', 20)->default('active')
                  ->comment('active, inactive, archived');

            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 3)->nullable()->comment('ISO 3166-1 alpha-3');
            $table->string('postal_code', 20)->nullable();

            $table->boolean('is_default')->default(false);
            $table->json('metadata')->nullable();

            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'warehouses_tenant_code_unique');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
