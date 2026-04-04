<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\PurchaseOrder\Domain\Entities\PurchaseOrder;
use Modules\SalesOrder\Domain\Entities\SalesOrder;

class PurchaseSalesOrderModuleTest extends TestCase
{
    private function makePO(string $status = 'draft'): PurchaseOrder
    {
        return new PurchaseOrder(1, 1, 1, 1, 'PO-001', $status, 500.00, 'USD', null, null, 1, [], null, null);
    }
    private function makeSO(string $status = 'draft'): SalesOrder
    {
        return new SalesOrder(1, 1, 1, 1, 'SO-001', $status, 450.00, 45.00, 495.00, 'USD', null, 1, [], null, null);
    }

    // PurchaseOrder tests
    public function test_po_creation(): void
    {
        $po = $this->makePO();
        $this->assertEquals('PO-001', $po->getPoNumber());
        $this->assertEquals('draft', $po->getStatus());
        $this->assertEquals(500.00, $po->getTotalAmount());
        $this->assertTrue($po->isDraft());
    }

    public function test_po_confirm(): void
    {
        $po = $this->makePO('draft');
        $po->confirm();
        $this->assertEquals('confirmed', $po->getStatus());
    }

    public function test_po_confirm_fails_if_not_draft(): void
    {
        $po = $this->makePO('confirmed');
        $this->expectException(\DomainException::class);
        $po->confirm();
    }

    public function test_po_cancel(): void
    {
        $po = $this->makePO('confirmed');
        $po->cancel();
        $this->assertEquals('cancelled', $po->getStatus());
    }

    public function test_po_cancel_fails_if_already_received(): void
    {
        $po = $this->makePO('received');
        $this->expectException(\DomainException::class);
        $po->cancel();
    }

    public function test_po_mark_received(): void
    {
        $po = $this->makePO('confirmed');
        $po->markReceived();
        $this->assertEquals('received', $po->getStatus());
    }

    public function test_po_mark_partially_received(): void
    {
        $po = $this->makePO('confirmed');
        $po->markPartiallyReceived();
        $this->assertEquals('partially_received', $po->getStatus());
    }

    // SalesOrder tests
    public function test_so_creation(): void
    {
        $so = $this->makeSO();
        $this->assertEquals('SO-001', $so->getSoNumber());
        $this->assertEquals('draft', $so->getStatus());
        $this->assertEquals(495.00, $so->getTotalAmount());
        $this->assertTrue($so->isDraft());
    }

    public function test_so_confirm(): void
    {
        $so = $this->makeSO('draft');
        $so->confirm();
        $this->assertEquals('confirmed', $so->getStatus());
    }

    public function test_so_confirm_fails_if_not_draft(): void
    {
        $so = $this->makeSO('confirmed');
        $this->expectException(\DomainException::class);
        $so->confirm();
    }

    public function test_so_outbound_flow(): void
    {
        $so = $this->makeSO('draft');
        $so->confirm();
        $this->assertEquals('confirmed', $so->getStatus());
        $so->startPicking();
        $this->assertEquals('picking', $so->getStatus());
        $so->startPacking();
        $this->assertEquals('packing', $so->getStatus());
        $so->ship();
        $this->assertEquals('shipped', $so->getStatus());
    }

    public function test_so_picking_requires_confirmed(): void
    {
        $so = $this->makeSO('draft');
        $this->expectException(\DomainException::class);
        $so->startPicking();
    }

    public function test_so_packing_requires_picking(): void
    {
        $so = $this->makeSO('confirmed');
        $this->expectException(\DomainException::class);
        $so->startPacking();
    }

    public function test_so_cancel(): void
    {
        $so = $this->makeSO('confirmed');
        $so->cancel();
        $this->assertEquals('cancelled', $so->getStatus());
    }

    public function test_so_cancel_fails_if_shipped(): void
    {
        $so = $this->makeSO('shipped');
        $this->expectException(\DomainException::class);
        $so->cancel();
    }
}
