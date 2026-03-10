<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->uuid('category_id')->nullable()->index();
            $table->string('name');
            $table->string('code')->index();
            $table->string('sku')->nullable()->index();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 4)->default(0);
            $table->decimal('cost', 15, 4)->nullable();
            $table->string('unit')->nullable();
            $table->enum('status', ['active', 'inactive', 'discontinued'])->default('active');
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'code']);
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
