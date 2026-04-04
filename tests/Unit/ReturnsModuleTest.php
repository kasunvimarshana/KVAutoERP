<?php
declare(strict_types=1);
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Modules\Returns\Domain\Entities\ReturnRequest;

class ReturnsModuleTest extends TestCase
{
    private function makeReturn(string $status = 'pending'): ReturnRequest
    {
        return new ReturnRequest(1, 1, 'purchase', 10, 'RET-001', $status, 'Damaged goods', null, null, [], null, null, null);
    }

    public function test_return_request_creation(): void
    {
        $ret = $this->makeReturn();
        $this->assertEquals('RET-001', $ret->getReturnNumber());
        $this->assertEquals('pending', $ret->getStatus());
        $this->assertEquals('purchase', $ret->getReturnType());
    }

    public function test_approve_return(): void
    {
        $ret = $this->makeReturn();
        $ret->approve(5);
        $this->assertEquals('approved', $ret->getStatus());
        $this->assertEquals(5, $ret->getProcessedBy());
        $this->assertNotNull($ret->getProcessedAt());
    }

    public function test_reject_return(): void
    {
        $ret = $this->makeReturn();
        $ret->reject(5);
        $this->assertEquals('rejected', $ret->getStatus());
        $this->assertEquals(5, $ret->getProcessedBy());
        $this->assertNotNull($ret->getProcessedAt());
    }

    public function test_approve_fails_if_not_pending(): void
    {
        $ret = $this->makeReturn('approved');
        $this->expectException(\DomainException::class);
        $ret->approve(5);
    }

    public function test_reject_fails_if_not_pending(): void
    {
        $ret = $this->makeReturn('rejected');
        $this->expectException(\DomainException::class);
        $ret->reject(5);
    }
}
