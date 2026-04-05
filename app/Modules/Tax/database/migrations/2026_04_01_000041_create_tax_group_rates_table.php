<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_group_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('tax_group_id');
            $table->string('name', 255);
            $table->decimal('rate', 8, 4);
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_compound')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tax_group_id')->references('id')->on('tax_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_group_rates');
    }
};
