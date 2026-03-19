<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_registries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('module_name', 200);
            $table->string('module_key', 200);
            $table->boolean('is_enabled')->default(true);
            $table->json('configuration')->nullable();
            $table->json('dependencies')->nullable();
            $table->string('version', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Each tenant can only register a module key once
            $table->unique(['tenant_id', 'module_key'], 'uq_module_tenant_key');
            $table->index(['tenant_id', 'is_enabled'], 'idx_module_tenant_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_registries');
    }
};
