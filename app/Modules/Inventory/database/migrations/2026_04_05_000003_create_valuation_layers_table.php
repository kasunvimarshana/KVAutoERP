<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('valuation_layers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->decimal('quantity', 16, 4);
            $table->decimal('remaining_quantity', 16, 4);
            $table->decimal('unit_cost', 20, 4);
            $table->timestamp('received_at');
            $table->string('batch_number', 100)->nullable()->index();
            $table->string('lot_number', 100)->nullable()->index();
            $table->date('expiry_date')->nullable()->index();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['tenant_id','product_id','warehouse_id','remaining_quantity']);
        });
    }
    public function down(): void { Schema::dropIfExists('valuation_layers'); }
};
