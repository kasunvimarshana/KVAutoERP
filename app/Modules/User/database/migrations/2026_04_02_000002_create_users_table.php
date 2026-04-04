<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('org_unit_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->string('locale')->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('active');
            $table->text('preferences')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
