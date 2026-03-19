<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('service_name', 100);
            $table->string('entity_type', 200);
            $table->json('fields');
            $table->json('validations')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'service_name', 'entity_type'], 'idx_form_tenant_service_entity');
            $table->index(['tenant_id', 'is_active'], 'idx_form_tenant_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_definitions');
    }
};
