<?php declare(strict_types=1);
namespace Tests\Unit;
use Modules\Order\Domain\Entities\Order;
use Modules\Order\Domain\Entities\OrderLine;
use Modules\Order\Domain\Entities\Return_;
use PHPUnit\Framework\TestCase;
class OrderModuleTest extends TestCase {
    public function test_order_entity(): void {
        $order = new Order(1, 1, 'SO-001', 'sales', 'draft', 42, 1, new \DateTimeImmutable(), 'USD', 1000.0, 100.0, 50.0, 1050.0, null);
        $this->assertSame('SO-001', $order->getOrderNumber());
        $this->assertSame('sales', $order->getType());
        $this->assertEqualsWithDelta(1050.0, $order->getTotalAmount(), 0.001);
    }
    public function test_order_line_entity(): void {
        $line = new OrderLine(1, 1, 5, null, 2.0, 100.0, 20.0, 0.0, 220.0, 'BATCH001', null, null);
        $this->assertSame(2.0, $line->getQuantity());
        $this->assertSame(220.0, $line->getLineTotal());
        $this->assertSame('BATCH001', $line->getBatchNumber());
    }
    public function test_return_entity(): void {
        $ret = new Return_(1, 1, 5, 'sales_return', 'pending', 'Damaged goods', 200.0, 'damaged', false, null);
        $this->assertSame('sales_return', $ret->getType());
        $this->assertFalse($ret->shouldRestockItems());
        $this->assertSame('damaged', $ret->getCondition());
    }
    public function test_order_statuses(): void {
        foreach (Order::STATUSES as $status) {
            $order = new Order(null, 1, 'ORD', 'purchase', $status, 1, null, new \DateTimeImmutable(), 'USD', 0.0, 0.0, 0.0, 0.0, null);
            $this->assertSame($status, $order->getStatus());
        }
    }
    public function test_order_types(): void {
        foreach (Order::TYPES as $type) {
            $order = new Order(null, 1, 'ORD', $type, 'draft', 1, null, new \DateTimeImmutable(), 'USD', 0.0, 0.0, 0.0, 0.0, null);
            $this->assertSame($type, $order->getType());
        }
    }
    public function test_order_lines_management(): void {
        $order = new Order(1, 1, 'SO-002', 'sales', 'draft', 10, null, new \DateTimeImmutable(), 'USD', 500.0, 50.0, 0.0, 550.0, null);
        $this->assertEmpty($order->getLines());
        $lines = [new OrderLine(1, 1, 1, null, 1.0, 500.0, 50.0, 0.0, 550.0, null, null, null)];
        $order->setLines($lines);
        $this->assertCount(1, $order->getLines());
    }
}
