<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->string('type', 50);
            $table->string('normal_balance', 10);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('accounting_accounts')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_accounts');
    }
};
