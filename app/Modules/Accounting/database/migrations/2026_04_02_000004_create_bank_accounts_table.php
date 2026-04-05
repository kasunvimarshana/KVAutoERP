<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_bank_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreignId('account_id')
                  ->constrained('accounting_accounts')
                  ->restrictOnDelete();
            $table->string('name', 255);
            $table->string('account_number', 100)->nullable();
            $table->string('account_type', 30);
            $table->string('currency_code', 10)->default('USD');
            $table->decimal('current_balance', 20, 6)->default(0);
            $table->dateTime('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('credentials')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_bank_accounts');
    }
};
