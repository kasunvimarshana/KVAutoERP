<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->string('slug', 100)->unique();
            $table->string('domain', 255)->nullable()->unique();
            $table->string('database_name', 100)->nullable();
            $table->enum('status', ['active', 'inactive', 'trial', 'suspended'])->default('trial');
            $table->json('settings')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('max_users')->default(100);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
