<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_refunds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('payment_id')
                  ->constrained('accounting_payments')
                  ->restrictOnDelete();
            $table->decimal('amount', 20, 6);
            $table->date('refund_date');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('journal_entry_id')
                  ->references('id')
                  ->on('accounting_journal_entries')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_refunds');
    }
};
