<?php declare(strict_types=1);
namespace Modules\HR\Infrastructure\Persistence\Eloquent\Repositories;
use Modules\HR\Domain\Entities\Employee;
use Modules\HR\Domain\RepositoryInterfaces\EmployeeRepositoryInterface;
use Modules\HR\Infrastructure\Persistence\Eloquent\Models\EmployeeModel;
class EloquentEmployeeRepository implements EmployeeRepositoryInterface {
    public function __construct(private readonly EmployeeModel $model) {}
    public function findById(int $id): ?Employee { $m=$this->model->newQuery()->find($id); return $m?$this->toEntity($m):null; }
    public function findByTenant(int $tenantId, ?string $status=null): array {
        $q=$this->model->newQuery()->where('tenant_id',$tenantId);
        if($status) $q->where('status',$status);
        return $q->get()->map(fn($m)=>$this->toEntity($m))->all();
    }
    public function findByDepartment(int $departmentId): array { return $this->model->newQuery()->where('department_id',$departmentId)->get()->map(fn($m)=>$this->toEntity($m))->all(); }
    public function save(Employee $e): Employee {
        $m=$e->getId()?$this->model->newQuery()->findOrFail($e->getId()):new EmployeeModel();
        $m->tenant_id=$e->getTenantId();$m->user_id=$e->getUserId();$m->employee_code=$e->getEmployeeCode();$m->first_name=$e->getFirstName();$m->last_name=$e->getLastName();$m->email=$e->getEmail();$m->phone=$e->getPhone();$m->department_id=$e->getDepartmentId();$m->position_id=$e->getPositionId();$m->hire_date=$e->getHireDate()->format('Y-m-d');$m->status=$e->getStatus();$m->basic_salary=$e->getBasicSalary();$m->salary_type=$e->getSalaryType();
        $m->save(); return $this->toEntity($m);
    }
    public function delete(int $id): void { $this->model->newQuery()->findOrFail($id)->delete(); }
    private function toEntity(EmployeeModel $m): Employee { return new Employee($m->id,$m->tenant_id,$m->user_id,$m->employee_code,$m->first_name,$m->last_name,$m->email,$m->phone,$m->department_id,$m->position_id,new \DateTimeImmutable($m->hire_date->toDateString()),$m->status,(float)$m->basic_salary,$m->salary_type); }
}
