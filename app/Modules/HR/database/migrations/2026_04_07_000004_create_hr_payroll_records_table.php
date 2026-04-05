<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hr_payroll_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('employee_id')->index();
            $table->unsignedSmallInteger('pay_period_year');
            $table->unsignedTinyInteger('pay_period_month');
            $table->decimal('basic_salary', 20, 4)->default(0);
            $table->decimal('allowances', 20, 4)->default(0);
            $table->decimal('deductions', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('net_pay', 20, 4)->default(0);
            $table->string('status', 20)->default('draft');
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['tenant_id','employee_id','pay_period_year','pay_period_month']);
        });
    }
    public function down(): void { Schema::dropIfExists('hr_payroll_records'); }
};
