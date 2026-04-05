<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('type', 20);
            $table->unsignedBigInteger('party_id')->index();
            $table->decimal('amount', 20, 4);
            $table->string('currency', 3)->default('USD');
            $table->date('payment_date');
            $table->string('method', 20)->default('cash');
            $table->string('reference', 100)->nullable();
            $table->string('status', 20)->default('draft');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
