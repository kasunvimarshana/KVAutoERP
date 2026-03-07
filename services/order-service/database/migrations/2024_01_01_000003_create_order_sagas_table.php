<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sagas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('order_id')->nullable()->index();
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'compensating', 'compensated'])->default('pending')->index();
            $table->string('current_step', 100)->nullable();
            $table->json('steps')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('order_sagas'); }
};
