<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('bank_account_id');
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 15, 4);
            $table->string('type', 10);
            $table->string('status', 20)->default('pending');
            $table->string('source', 20)->default('manual');
            $table->uuid('category_id')->nullable();
            $table->uuid('journal_entry_id')->nullable();
            $table->string('reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('expense_categories')->nullOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('bank_transactions'); }
};
