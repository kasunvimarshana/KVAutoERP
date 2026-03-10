<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create Tenants table.
 * 
 * Stores all tenant data including dynamic configurations that can be
 * updated at runtime without restarting the service.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable();
            $table->string('plan')->default('free'); // free, starter, pro, enterprise
            $table->boolean('is_active')->default(true);
            
            // Dynamic database connection settings (for tenant DB isolation)
            $table->string('db_host')->nullable();
            $table->unsignedSmallInteger('db_port')->default(3306);
            $table->string('db_name')->nullable();
            $table->string('db_username')->nullable();
            $table->string('db_password')->nullable(); // Should be encrypted at DB level
            
            // Dynamic cache settings
            $table->string('cache_driver')->default('redis'); // redis, memcached, array
            
            // Dynamic queue settings  
            $table->string('queue_driver')->default('rabbitmq'); // rabbitmq, kafka, sync
            
            // Dynamic mail settings
            $table->string('mail_driver')->default('smtp');
            $table->string('mail_host')->nullable();
            $table->unsignedSmallInteger('mail_port')->default(587);
            $table->string('mail_username')->nullable();
            $table->string('mail_password')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->string('mail_from_name')->nullable();
            
            // API keys (encrypted JSON)
            $table->text('api_keys')->nullable(); // Encrypted
            
            // Feature flags (JSON) - can be toggled at runtime
            $table->json('feature_flags')->nullable();
            
            // Webhook configuration
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret')->nullable();
            
            // General settings (JSON)
            $table->json('settings')->nullable();
            
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['slug', 'is_active']);
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
