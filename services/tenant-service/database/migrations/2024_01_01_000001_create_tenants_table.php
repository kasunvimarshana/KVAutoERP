<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->string('domain', 255)->nullable()->unique();
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])->default('free');
            $table->json('settings')->nullable();
            $table->json('config')->nullable();
            $table->json('database_config')->nullable();
            $table->json('mail_config')->nullable();
            $table->json('cache_config')->nullable();
            $table->json('broker_config')->nullable();
            $table->unsignedInteger('max_users')->default(100);
            $table->unsignedInteger('max_organizations')->default(10);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('slug');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
