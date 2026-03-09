<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the tenants table for multi-tenant organization management.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->enum('plan', ['free', 'starter', 'pro', 'enterprise'])->default('free');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->string('database_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tenant_configurations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key');           // Dot-notation Laravel config key
            $table->text('value');           // JSON-encoded value
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_configurations');
        Schema::dropIfExists('tenants');
    }
};
