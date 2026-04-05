<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Models;
use Modules\Core\Infrastructure\Persistence\Eloquent\Models\BaseModel;
class EmployeeModel extends BaseModel {
    protected $table = 'hr_employees';
    protected $fillable = ['tenant_id','user_id','employee_code','first_name','last_name','email','phone','department_id','position_id','hire_date','status','basic_salary','salary_type'];
    protected $casts = ['basic_salary'=>'float','hire_date'=>'date','created_at'=>'datetime','updated_at'=>'datetime','deleted_at'=>'datetime','id'=>'int'];
}
