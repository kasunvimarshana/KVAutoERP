<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create roles, permissions, and pivot tables for Spatie Laravel Permission.
 *
 * Extends the standard Spatie schema with a tenant_id column on the roles
 * table to enable multi-tenant RBAC scoping.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── permissions ──────────────────────────────────────────────────
        Schema::create('permissions', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        // ── roles ────────────────────────────────────────────────────────
        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->uuid('tenant_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            // Roles are unique per name + guard + tenant combination.
            $table->unique(['name', 'guard_name', 'tenant_id']);
            $table->index('tenant_id');
        });

        // ── model_has_permissions ────────────────────────────────────────
        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('tenant_id')->nullable();

            $table->primary(['permission_id', 'model_id', 'model_type']);

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();

            $table->index(['model_id', 'model_type']);
            $table->index('tenant_id');
        });

        // ── model_has_roles ──────────────────────────────────────────────
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('tenant_id')->nullable();

            $table->primary(['role_id', 'model_id', 'model_type']);

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();

            $table->index(['model_id', 'model_type']);
            $table->index('tenant_id');
        });

        // ── role_has_permissions ─────────────────────────────────────────
        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->cascadeOnDelete();

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->cascadeOnDelete();

            $table->primary(['permission_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
