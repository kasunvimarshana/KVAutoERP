<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('title');
            $table->unsignedBigInteger('contact_id')->index();
            $table->string('stage', 30)->default('prospecting')->index();
            $table->decimal('value', 20, 4)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->decimal('probability', 5, 2)->default(0);
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('opportunities'); }
};
