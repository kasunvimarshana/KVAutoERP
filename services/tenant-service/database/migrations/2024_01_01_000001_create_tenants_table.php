<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->unique()->nullable();
            $table->string('email');
            $table->string('phone')->nullable();

            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])
                  ->default('trial');

            $table->enum('plan', ['free', 'starter', 'professional', 'enterprise'])
                  ->default('free');

            // Tenant-specific configuration blobs (stored as JSON)
            $table->json('settings')->nullable();
            $table->json('db_config')->nullable();
            $table->json('cache_config')->nullable();
            $table->json('mail_config')->nullable();
            $table->json('broker_config')->nullable();

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Commonly queried columns
            $table->index('status');
            $table->index('plan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
