<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('terminal_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->decimal('opening_cash', 20, 4)->default(0);
            $table->decimal('closing_cash', 20, 4)->nullable();
            $table->string('status', 20)->default('open')->index();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pos_sessions'); }
};
