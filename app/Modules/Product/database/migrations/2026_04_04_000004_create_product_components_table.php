<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_product_id')->index();
            $table->unsignedBigInteger('component_product_id')->index();
            $table->decimal('quantity', 16, 4)->default(1);
            $table->string('unit', 20)->default('unit');
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('product_components'); }
};
