<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->string('code', 50);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('path', 500)->default('/');
            $table->unsignedTinyInteger('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','code']);
            $table->index('path');
        });
    }
    public function down(): void { Schema::dropIfExists('product_categories'); }
};
