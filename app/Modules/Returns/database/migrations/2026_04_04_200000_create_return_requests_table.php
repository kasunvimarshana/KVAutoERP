<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('tenant_id'); $table->string('return_type');
            $table->unsignedBigInteger('reference_id'); $table->string('return_number');
            $table->string('status')->default('pending'); $table->string('reason');
            $table->text('notes')->nullable(); $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps(); $table->softDeletes(); $table->unique(['tenant_id','return_number']); $table->index('tenant_id');
        });
    }
    public function down(): void { Schema::dropIfExists('return_requests'); }
};
