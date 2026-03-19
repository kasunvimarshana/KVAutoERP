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
        Schema::create('user_roles', static function (Blueprint $table): void {
            $table->uuid('user_profile_id');
            $table->uuid('role_id');
            $table->uuid('tenant_id')->index();
            $table->uuid('granted_by')->nullable();
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->primary(['user_profile_id', 'role_id']);

            $table->foreign('user_profile_id')
                  ->references('id')
                  ->on('user_profiles')
                  ->cascadeOnDelete();

            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();

            $table->index(['tenant_id', 'user_profile_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
