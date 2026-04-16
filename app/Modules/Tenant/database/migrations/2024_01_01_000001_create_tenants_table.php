<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('domain')->unique()->nullable()->index();
            $table->string('logo_path')->nullable();
            $table->json('database_config')->nullable();
            $table->json('mail_config')->nullable();
            $table->json('cache_config')->nullable();
            $table->json('queue_config')->nullable();
            $table->json('feature_flags')->nullable();
            $table->json('api_keys')->nullable();
            $table->json('settings')->nullable();
            $table->string('plan')->default('free');
            $table->foreignId('tenant_plan_id')->nullable()->constrained('tenant_plans')->nullOnDelete();
            $table->enum('status', ['active', 'suspended', 'pending', 'cancelled'])
                ->default('active')
                ->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common query patterns
            $table->index(['status', 'active']);
            $table->index(['tenant_plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};