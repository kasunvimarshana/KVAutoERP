<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name', 255);
            $table->foreignId('account_id')
                  ->constrained('accounting_accounts')
                  ->restrictOnDelete();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('accounting_expense_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_expense_categories');
    }
};
