<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->string('entry_number');
            $table->date('date');
            $table->string('description');
            $table->string('reference')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('total_debit', 15, 4);
            $table->decimal('total_credit', 15, 4);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'entry_number']);
        });
    }

    public function down(): void { Schema::dropIfExists('journal_entries'); }
};
