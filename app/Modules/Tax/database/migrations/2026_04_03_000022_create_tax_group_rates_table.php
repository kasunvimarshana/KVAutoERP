<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_group_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('tax_group_id');
            $table->unsignedBigInteger('tax_rate_id');
            $table->integer('sort_order')->default(0);
            $table->foreign('tax_group_id')
                ->references('id')
                ->on('tax_groups')
                ->cascadeOnDelete();
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->cascadeOnDelete();
            $table->timestamps();
            // No softDeletes for this join table
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_group_rates');
    }
};
