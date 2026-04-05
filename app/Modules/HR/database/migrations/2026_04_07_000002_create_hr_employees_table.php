<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('employee_code', 50);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->unsignedBigInteger('department_id')->index();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->date('hire_date');
            $table->string('status', 20)->default('active')->index();
            $table->decimal('basic_salary', 20, 4)->default(0);
            $table->string('salary_type', 20)->default('monthly');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','employee_code']);
            $table->unique(['tenant_id','email']);
        });
    }
    public function down(): void { Schema::dropIfExists('hr_employees'); }
};
