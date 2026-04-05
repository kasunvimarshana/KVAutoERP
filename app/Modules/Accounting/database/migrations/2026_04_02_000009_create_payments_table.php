<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('payment_number');
            $table->date('payment_date');
            $table->decimal('amount', 15, 4);
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method', 30);
            $table->uuid('from_account_id')->nullable();
            $table->uuid('to_account_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->uuid('journal_entry_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'payment_number']);
            $table->foreign('from_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('to_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('payments'); }
};
