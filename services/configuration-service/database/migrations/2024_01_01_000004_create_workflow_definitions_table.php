<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('name', 200);
            $table->string('entity_type', 200);
            $table->json('states');
            $table->json('transitions');
            $table->json('guards')->nullable();
            $table->json('actions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('version')->default(1);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique name per tenant
            $table->unique(['tenant_id', 'name'], 'uq_workflow_tenant_name');
            $table->index(['tenant_id', 'entity_type'], 'idx_workflow_tenant_entity');
            $table->index(['tenant_id', 'is_active'], 'idx_workflow_tenant_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_definitions');
    }
};
