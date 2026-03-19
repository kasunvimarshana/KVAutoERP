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
        Schema::create('user_profiles', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('organization_id')->nullable()->index();
            $table->uuid('branch_id')->nullable()->index();
            $table->uuid('location_id')->nullable()->index();
            $table->uuid('department_id')->nullable()->index();
            $table->uuid('auth_user_id')->index();
            $table->string('email', 255);
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('display_name', 150)->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('locale', 10)->nullable()->default('en');
            $table->string('timezone', 50)->nullable()->default('UTC');
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Email is unique per tenant.
            $table->unique(['tenant_id', 'email']);
            // Auth user ID is unique per tenant.
            $table->unique(['tenant_id', 'auth_user_id']);

            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
