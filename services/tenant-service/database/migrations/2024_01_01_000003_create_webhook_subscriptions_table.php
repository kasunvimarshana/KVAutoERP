<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('url', 2048);
            $table->json('events');
            $table->string('secret', 255);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('retry_count')->default(3);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')
                  ->references('id')
                  ->on('tenants')
                  ->cascadeOnDelete();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_subscriptions');
    }
};
