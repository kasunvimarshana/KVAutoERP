<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action', 100)->index();
            $table->string('entity_type', 100)->index();
            $table->string('entity_id', 36)->index();
            $table->string('actor_id', 36)->nullable()->index();
            $table->string('tenant_id', 36)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // No updated_at — append-only, tamper-evident log
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
