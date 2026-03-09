<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name', 255);
            $table->string('email', 255);
            $table->string('password');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('two_factor_secret')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->json('device_tokens')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->json('metadata')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Enforce unique email per tenant (not globally unique)
            $table->unique(['tenant_id', 'email']);

            $table->index(['tenant_id', 'status']);
            $table->index('email');
            $table->index('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
