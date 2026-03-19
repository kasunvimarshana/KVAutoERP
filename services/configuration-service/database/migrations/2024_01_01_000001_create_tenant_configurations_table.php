<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('service_name', 100);
            $table->string('config_key', 200);
            $table->json('config_value');
            $table->string('config_type', 20)->default('string');
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('description', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Tenant-scoped unique: each service can only have one entry per key
            $table->unique(['tenant_id', 'service_name', 'config_key'], 'uq_tenant_service_config_key');
            $table->index(['tenant_id', 'service_name'], 'idx_tenant_service');
            $table->index(['tenant_id', 'is_active'], 'idx_tenant_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_configurations');
    }
};
