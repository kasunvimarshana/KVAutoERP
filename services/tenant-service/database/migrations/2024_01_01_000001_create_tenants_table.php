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
        Schema::create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 63)->unique();
            $table->string('domain', 255)->nullable()->unique();
            $table->string('database_name', 64)->unique();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->string('plan', 50)->default('starter')->index();
            $table->string('billing_email', 255);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'plan']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
