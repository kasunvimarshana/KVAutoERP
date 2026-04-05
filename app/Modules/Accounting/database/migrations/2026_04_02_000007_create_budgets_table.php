<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_budgets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name', 255);
            $table->foreignId('account_id')
                  ->constrained('accounting_accounts')
                  ->restrictOnDelete();
            $table->string('period_type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 20, 6);
            $table->decimal('spent', 20, 6)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_budgets');
    }
};
