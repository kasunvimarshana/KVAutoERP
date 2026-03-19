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
        Schema::create('role_permissions', static function (Blueprint $table): void {
            $table->uuid('role_id');
            $table->uuid('permission_id');
            $table->uuid('tenant_id')->index();
            $table->timestamps();

            $table->primary(['role_id', 'permission_id']);

            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->cascadeOnDelete();

            $table->foreign('permission_id')
                  ->references('id')
                  ->on('permissions')
                  ->cascadeOnDelete();

            $table->index(['tenant_id', 'role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
