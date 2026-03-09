<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_deliveries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('subscription_id');
            $table->uuid('tenant_id');
            $table->string('event', 100);
            $table->longText('payload')->nullable();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('webhook_subscriptions')
                  ->cascadeOnDelete();

            $table->index(['subscription_id', 'created_at']);
            $table->index(['tenant_id', 'event']);
            $table->index('response_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
