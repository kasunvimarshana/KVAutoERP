<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('type', 50)->default('customer');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('position')->nullable();
            $table->json('address')->nullable();
            $table->string('tax_number', 100)->nullable();
            $table->string('currency_code', 10)->default('USD');
            $table->decimal('credit_limit', 20, 6)->default(0);
            $table->integer('payment_terms')->default(30);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'type']);
        });
    }
    public function down(): void { Schema::dropIfExists('contacts'); }
};
