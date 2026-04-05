<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('refund_number');
            $table->date('refund_date');
            $table->decimal('amount', 15, 4);
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method', 30);
            $table->uuid('account_id')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->uuid('original_payment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'refund_number']);
            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('original_payment_id')->references('id')->on('payments')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('refunds'); }
};
