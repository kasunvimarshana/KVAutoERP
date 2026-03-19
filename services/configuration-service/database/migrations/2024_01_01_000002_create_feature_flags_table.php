<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id');
            $table->string('flag_key', 200);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedTinyInteger('rollout_percentage')->default(100);
            $table->json('conditions')->nullable();
            $table->string('description', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Each tenant can only have one flag per key
            $table->unique(['tenant_id', 'flag_key'], 'uq_tenant_flag_key');
            $table->index(['tenant_id', 'is_enabled'], 'idx_tenant_flag_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};
