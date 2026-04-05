<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('symbology', 50);
            $table->string('data', 500);
            $table->string('check_digit', 10)->nullable();
            $table->text('encoded_data');
            $table->timestamp('generated_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'symbology']);
            $table->index(['tenant_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};
