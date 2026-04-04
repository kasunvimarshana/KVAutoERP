<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('return_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_request_id')->constrained('return_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id'); $table->decimal('quantity_returned',15,4);
            $table->decimal('unit_price',15,4)->default(0);
            $table->string('batch_number')->nullable(); $table->string('lot_number')->nullable();
            $table->string('serial_number')->nullable(); $table->text('reason')->nullable();
            $table->string('condition')->nullable();
            $table->index('return_request_id');
        });
    }
    public function down(): void { Schema::dropIfExists('return_lines'); }
};
