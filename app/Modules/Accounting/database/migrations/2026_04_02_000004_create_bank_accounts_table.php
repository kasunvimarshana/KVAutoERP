<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_type', 30);
            $table->decimal('balance', 15, 4)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->uuid('chart_of_account_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('chart_of_account_id')->references('id')->on('accounts')->nullOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('bank_accounts'); }
};
