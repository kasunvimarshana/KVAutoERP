<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->unsignedBigInteger('opportunity_id')->nullable()->constrained('opportunities')->nullOnDelete();
            $table->unsignedBigInteger('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('type', 50)->default('task');
            $table->string('subject');
            $table->text('description')->nullable();
            $table->string('status', 50)->default('planned');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('activities'); }
};
