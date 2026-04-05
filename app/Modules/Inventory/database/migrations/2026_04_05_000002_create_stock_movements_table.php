<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->string('type', 30)->index();
            $table->decimal('quantity', 16, 4);
            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->string('reference', 100)->index();
            $table->string('batch_number', 100)->nullable()->index();
            $table->string('lot_number', 100)->nullable()->index();
            $table->string('serial_number', 100)->nullable()->index();
            $table->date('expiry_date')->nullable();
            $table->timestamp('moved_at');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('stock_movements'); }
};
