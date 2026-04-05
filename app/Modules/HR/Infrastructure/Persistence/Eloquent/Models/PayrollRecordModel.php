<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class PayrollRecordModel extends BaseModel {
    protected $table = 'hr_payroll_records';
    protected $fillable = ['tenant_id','employee_id','pay_period_year','pay_period_month','basic_salary','allowances','deductions','tax_amount','net_pay','status'];
    protected $casts = ['basic_salary'=>'float','allowances'=>'float','deductions'=>'float','tax_amount'=>'float','net_pay'=>'float','pay_period_year'=>'int','pay_period_month'=>'int','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
