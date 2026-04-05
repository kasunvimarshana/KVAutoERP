<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('code');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('path', 1000)->default('');
            $table->integer('level')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['tenant_id', 'code']);
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
