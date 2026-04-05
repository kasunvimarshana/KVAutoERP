<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('journal_entry_id')
                  ->constrained('accounting_journal_entries')
                  ->cascadeOnDelete();
            $table->foreignId('account_id')
                  ->constrained('accounting_accounts')
                  ->restrictOnDelete();
            $table->string('description', 500)->nullable();
            $table->decimal('debit', 20, 6)->default(0);
            $table->decimal('credit', 20, 6)->default(0);
            $table->timestamps();
            // No soft-deletes: lines are immutable; void the entry instead
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_lines');
    }
};
