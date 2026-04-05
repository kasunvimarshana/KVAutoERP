<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('name');
            $table->string('code', 100);
            $table->string('type', 30)->default('bin');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->string('path', 500)->default('/');
            $table->unsignedTinyInteger('level')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_pickable')->default(true);
            $table->boolean('is_receivable')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['warehouse_id','code']);
            $table->index('path');
        });
    }
    public function down(): void { Schema::dropIfExists('warehouse_locations'); }
};
