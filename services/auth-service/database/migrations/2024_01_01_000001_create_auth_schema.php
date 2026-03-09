<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auth Service Schema Migration.
 *
 * Creates all tables for the auth-service:
 *   - tenants
 *   - users
 *   - roles
 *   - permissions
 *   - user_roles
 *   - user_permissions
 *   - role_permissions
 *   - abac_policies
 */
return new class extends Migration
{
    public function up(): void
    {
        // =====================================================================
        // Tenants
        // =====================================================================
        Schema::create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->string('database_name')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->string('plan', 50)->default('starter');
            $table->json('configuration')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['slug', 'status']);
        });

        // =====================================================================
        // Users
        // =====================================================================
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status', 20)->default('active')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
        });

        // =====================================================================
        // Roles (RBAC)
        // =====================================================================
        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('guard_name', 50)->default('api');
            $table->timestamps();

            $table->unique(['tenant_id', 'name', 'guard_name']);
        });

        // =====================================================================
        // Permissions (RBAC)
        // =====================================================================
        Schema::create('permissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('guard_name', 50)->default('api');
            $table->string('resource', 100)->nullable()->index();
            $table->string('action', 50)->nullable()->index();
            $table->timestamps();
        });

        // =====================================================================
        // User ↔ Role pivot (RBAC)
        // =====================================================================
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'role_id']);
        });

        // =====================================================================
        // User ↔ Permission pivot (direct permissions)
        // =====================================================================
        Schema::create('user_permissions', function (Blueprint $table): void {
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['user_id', 'permission_id']);
        });

        // =====================================================================
        // Role ↔ Permission pivot
        // =====================================================================
        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignUuid('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'permission_id']);
        });

        // =====================================================================
        // ABAC Policies
        // =====================================================================
        Schema::create('abac_policies', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('resource', 100)->index();
            $table->string('action', 50)->index();
            $table->json('conditions');
            $table->boolean('is_active')->default(true)->index();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['resource', 'action', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abac_policies');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tenants');
    }
};
