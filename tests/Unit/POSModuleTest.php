<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\POS\Domain\Entities\POSSession;
use Modules\POS\Domain\Entities\POSTransaction;
use Modules\POS\Domain\Entities\Terminal;
use PHPUnit\Framework\TestCase;
class POSModuleTest extends TestCase {
    public function test_terminal_entity(): void {
        $t = new Terminal(1, 1, 'Cashier 1', 'POS-01', 1, true);
        $this->assertSame('POS-01', $t->getCode());
        $this->assertTrue($t->isActive());
    }
    public function test_session_is_open(): void {
        $s = new POSSession(1, 1, 1, 5, 100.0, null, 'open', new \DateTimeImmutable(), null);
        $this->assertTrue($s->isOpen());
        $this->assertNull($s->getClosingCash());
    }
    public function test_session_closed(): void {
        $s = new POSSession(1, 1, 1, 5, 100.0, 350.0, 'closed', new \DateTimeImmutable('-1 hour'), new \DateTimeImmutable());
        $this->assertFalse($s->isOpen());
        $this->assertSame(350.0, $s->getClosingCash());
    }
    public function test_pos_transaction(): void {
        $t = new POSTransaction(1, 1, 1, 'TXN-001', 200.0, 20.0, 10.0, 210.0, 250.0, 40.0, 'cash', 'completed');
        $this->assertSame('completed', $t->getStatus());
        $this->assertSame(40.0, $t->getChange());
    }
}
