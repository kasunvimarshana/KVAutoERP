<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\CRM\Domain\Entities\Activity;
use Modules\CRM\Domain\Entities\Contact;
use Modules\CRM\Domain\Entities\Lead;
use Modules\CRM\Domain\Entities\Opportunity;
use PHPUnit\Framework\TestCase;
class CRMModuleTest extends TestCase {
    public function test_contact_entity(): void {
        $c = new Contact(1, 1, 'customer', 'Alice Smith', 'alice@example.com', '+1234567890', 'Acme', '123 Main St', true, null);
        $this->assertSame('Alice Smith', $c->getName());
        $this->assertSame('customer', $c->getType());
    }
    public function test_lead_entity(): void {
        $l = new Lead(1, 1, 'New CRM Deal', 5, 'qualified', 10000.0, 'USD', 3, new \DateTimeImmutable('+30 days'));
        $this->assertFalse($l->isConverted());
        $this->assertSame(10000.0, $l->getValue());
    }
    public function test_lead_converted(): void {
        $l = new Lead(1, 1, 'Done', 5, 'converted', 5000.0, 'USD', null, null);
        $this->assertTrue($l->isConverted());
    }
    public function test_opportunity_weighted_value(): void {
        $o = new Opportunity(1, 1, 'Big Deal', 5, 'proposal', 100000.0, 'USD', 60.0, 2, new \DateTimeImmutable('+60 days'));
        $this->assertEqualsWithDelta(60000.0, $o->getWeightedValue(), 0.001);
    }
    public function test_activity_is_completed(): void {
        $a = new Activity(1, 1, 'call', 'Follow-up call', null, 5, 'lead', 'completed', new \DateTimeImmutable(), new \DateTimeImmutable(), 3);
        $this->assertTrue($a->isCompleted());
    }
}
