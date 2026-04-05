<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('barcode_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('label_template_id')->nullable();
            $table->unsignedBigInteger('barcode_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('printer_id', 100)->nullable();
            $table->unsignedSmallInteger('copies')->default(1);
            $table->timestamp('printed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_print_jobs');
    }
};
