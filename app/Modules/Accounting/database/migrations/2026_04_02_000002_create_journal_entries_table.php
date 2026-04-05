<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_entries', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('reference_no', 100);
            $table->date('date')->index();
            $table->string('description', 500);
            $table->string('status', 20)->default('draft');
            $table->string('type', 50);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entries');
    }
};
