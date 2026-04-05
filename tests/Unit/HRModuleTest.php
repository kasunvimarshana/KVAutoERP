<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\HR\Domain\Entities\Department;
use Modules\HR\Domain\Entities\Employee;
use Modules\HR\Domain\Entities\LeaveRequest;
use Modules\HR\Domain\Entities\PayrollRecord;
use PHPUnit\Framework\TestCase;
class HRModuleTest extends TestCase {
    public function test_department_entity(): void {
        $d = new Department(1, 1, 'Engineering', 'ENG', null, 5, true);
        $this->assertSame('Engineering', $d->getName());
        $this->assertTrue($d->isActive());
    }
    public function test_employee_full_name(): void {
        $e = new Employee(1, 1, 10, 'EMP001', 'John', 'Doe', 'john@example.com', null, 1, null, new \DateTimeImmutable('2020-01-01'), 'active', 5000.0, 'monthly');
        $this->assertSame('John Doe', $e->getFullName());
        $this->assertTrue($e->isActive());
    }
    public function test_employee_terminated_not_active(): void {
        $e = new Employee(1, 1, 10, 'EMP002', 'Jane', 'Doe', 'jane@example.com', null, 1, null, new \DateTimeImmutable(), 'terminated', 0.0, 'monthly');
        $this->assertFalse($e->isActive());
    }
    public function test_leave_request(): void {
        $lr = new LeaveRequest(1, 1, 5, 'annual', new \DateTimeImmutable('2026-06-01'), new \DateTimeImmutable('2026-06-10'), 10.0, 'pending', 'Summer vacation', null);
        $this->assertSame(10.0, $lr->getDays());
        $this->assertFalse($lr->isApproved());
    }
    public function test_payroll_record(): void {
        $pr = new PayrollRecord(1, 1, 5, 2026, 1, 5000.0, 500.0, 200.0, 300.0, 5000.0, 'approved');
        $this->assertSame(5000.0, $pr->getNetPay());
        $this->assertSame(2026, $pr->getPayPeriodYear());
    }
}
