<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saga_states', function (Blueprint $table) {
            $table->id();
            $table->uuid('saga_id')->unique();

            $table->enum('status', [
                'started',
                'running',
                'completed',
                'compensating',
                'compensated',
                'failed',
            ])->default('started')->index();

            // Original request data that kicked off the saga
            $table->json('payload');

            // Mutable state accumulated during execution
            $table->json('context')->nullable();

            // Tracking arrays
            $table->json('completed_steps')->nullable();
            $table->json('compensated_steps')->nullable();
            $table->json('events')->nullable();

            // Human-readable failure description
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saga_states');
    }
};
