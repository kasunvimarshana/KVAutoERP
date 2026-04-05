<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('reference_no', 100);
            $table->date('date');
            $table->decimal('amount', 20, 6);
            $table->string('currency_code', 10)->default('USD');
            $table->string('payment_method', 30);
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->string('payable_type', 255);
            $table->unsignedBigInteger('payable_id');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['payable_type', 'payable_id']);

            $table->foreign('bank_account_id')
                  ->references('id')
                  ->on('accounting_bank_accounts')
                  ->nullOnDelete();
            $table->foreign('journal_entry_id')
                  ->references('id')
                  ->on('accounting_journal_entries')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_payments');
    }
};
