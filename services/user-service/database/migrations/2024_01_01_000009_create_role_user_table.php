<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('user_id')->index();
            $table->uuid('role_id')->index();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('assigned_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'role_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
