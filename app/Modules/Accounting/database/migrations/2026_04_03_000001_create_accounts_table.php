<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('code', 20);
            $table->string('name');
            $table->string('type', 20)->index();
            $table->string('sub_type', 50);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->string('normal_balance', 6)->default('debit');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('accounts'); }
};
