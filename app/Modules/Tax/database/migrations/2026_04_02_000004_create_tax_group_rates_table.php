<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tax_group_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tax_group_id')->index();
            $table->string('name');
            $table->decimal('rate', 8, 4);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tax_group_rates'); }
};
