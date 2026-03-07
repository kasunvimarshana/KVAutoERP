<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('channel');           // email, slack, webhook, push
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->string('template')->nullable();
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('event_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['channel', 'status']);
        });

        Schema::create('webhook_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->string('url');
            $table->json('events');
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webhook_id')->index();
            $table->string('tenant_id')->index();
            $table->string('event');
            $table->json('payload')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->boolean('delivered')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('webhook_id')->references('id')->on('webhook_registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('webhook_registrations');
        Schema::dropIfExists('notifications');
    }
};
