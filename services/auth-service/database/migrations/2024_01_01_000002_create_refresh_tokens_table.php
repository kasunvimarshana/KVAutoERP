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
        Schema::create('refresh_tokens', function (Blueprint $table): void {
            // UUID primary key
            $table->uuid('id')->primary();

            // Owner FK — cascade deletes so tokens are removed with the user
            $table->uuid('user_id')->index();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Tenant denormalised for fast tenant-scoped queries
            $table->uuid('tenant_id')->index();

            // Device that owns this refresh token
            $table->string('device_id', 255)->index();

            // SHA-256 hash of the raw refresh token (never store plaintext)
            $table->string('token_hash', 64)->unique();

            // Lifecycle timestamps
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();

            // Only created_at; refresh tokens are never updated
            $table->timestamp('created_at')->useCurrent();

            // Composite index for device-level revocation queries
            $table->index(['user_id', 'device_id'], 'rt_user_device_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refresh_tokens');
    }
};
