<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('transaction_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->string('apply_to', 10)->default('all');
            $table->string('match_field', 30)->default('description');
            $table->string('match_value');
            $table->unsignedBigInteger('category_account_id');
            $table->unsignedSmallInteger('priority')->default(100);
            $table->softDeletes();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transaction_rules'); }
};
