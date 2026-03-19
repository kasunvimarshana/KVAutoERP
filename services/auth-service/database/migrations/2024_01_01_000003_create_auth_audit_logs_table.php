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
        Schema::create('auth_audit_logs', function (Blueprint $table): void {
            // Bigint auto-increment for fast sequential append writes.
            // Intentionally NOT a UUID — sequential IDs optimise INSERT performance
            // on high-volume audit tables.
            $table->bigIncrements('id');

            // Nullable because we log failed attempts even when user is unknown
            $table->uuid('user_id')->nullable()->index();
            $table->uuid('tenant_id')->nullable()->index();

            // Event classification
            $table->string('event_type', 100)->index();

            // Request context
            $table->string('ip_address', 45)->nullable();   // supports IPv6
            $table->string('user_agent', 512)->nullable();
            $table->string('device_id', 255)->nullable();

            // Structured metadata (email attempted, jti revoked, etc.)
            $table->json('metadata')->nullable();

            // Only created_at — audit records are immutable (no updated_at)
            $table->timestamp('created_at')->useCurrent()->index();

            // NOTE: No foreign key on user_id intentionally — audit records
            // must survive user deletion for regulatory compliance.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_audit_logs');
    }
};
