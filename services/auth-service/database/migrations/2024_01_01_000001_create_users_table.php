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
        Schema::create('users', function (Blueprint $table): void {
            // UUID primary key
            $table->uuid('id')->primary();

            // Multi-tenant hierarchy
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();

            // Identity
            $table->string('email', 255);
            $table->string('password');
            $table->string('first_name', 100);
            $table->string('last_name', 100);

            // RBAC
            $table->json('roles')->nullable()->comment('Array of role slugs');
            $table->json('permissions')->nullable()->comment('Array of permission slugs');

            // Session / device tracking
            $table->json('device_sessions')->nullable()->comment('Active device session metadata');

            // Token versioning for global logout
            $table->unsignedInteger('token_version')->default(1);

            // Account state
            $table->boolean('is_active')->default(true)->index();

            // Audit columns
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();

            $table->timestamps();

            // Email must be unique per tenant
            $table->unique(['tenant_id', 'email'], 'users_tenant_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
