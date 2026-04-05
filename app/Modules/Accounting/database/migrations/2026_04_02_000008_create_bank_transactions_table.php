<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_bank_transactions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('bank_account_id')
                  ->constrained('accounting_bank_accounts')
                  ->cascadeOnDelete();
            $table->date('date')->index();
            $table->decimal('amount', 20, 6);
            $table->string('type', 10);
            $table->string('description', 500);
            $table->string('reference', 255)->nullable();
            $table->string('source', 20)->default('manual');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('transaction_rule_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounting_accounts')
                  ->nullOnDelete();
            $table->foreign('transaction_rule_id')
                  ->references('id')
                  ->on('accounting_transaction_rules')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_bank_transactions');
    }
};
