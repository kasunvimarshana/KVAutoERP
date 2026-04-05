<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('cycle_count_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cycle_count_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->decimal('system_quantity', 16, 4)->default(0);
            $table->decimal('counted_quantity', 16, 4)->nullable();
            $table->decimal('variance', 16, 4)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cycle_count_lines'); }
};
