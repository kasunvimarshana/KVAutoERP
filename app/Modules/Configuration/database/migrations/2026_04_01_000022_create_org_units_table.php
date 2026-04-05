<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('code', 50);
            $table->string('type', 30);
            $table->uuid('parent_id')->nullable();
            $table->string('path', 1000)->default('/');
            $table->unsignedSmallInteger('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->foreign('parent_id')->references('id')->on('org_units')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_units');
    }
};
