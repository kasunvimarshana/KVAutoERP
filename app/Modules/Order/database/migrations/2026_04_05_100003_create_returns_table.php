<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('original_order_id')->index();
            $table->string('type', 30);
            $table->string('status', 30)->default('pending');
            $table->text('reason');
            $table->decimal('refund_amount', 20, 4)->default(0);
            $table->string('condition', 20)->default('good');
            $table->boolean('restock_items')->default(true);
            $table->timestamp('processed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('returns'); }
};
