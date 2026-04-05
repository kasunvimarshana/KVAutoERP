<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bank_account_id')->index();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('type', 10);
            $table->decimal('amount', 20, 4);
            $table->date('transaction_date');
            $table->text('description');
            $table->string('status', 20)->default('pending');
            $table->string('source', 20)->default('manual');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('bank_transactions'); }
};
