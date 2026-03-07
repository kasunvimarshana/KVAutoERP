<?php

namespace Tests\Unit\Order;

use App\Modules\Order\DTOs\OrderDTO;
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Repositories\Interfaces\OrderRepositoryInterface;
use App\Modules\Order\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    private OrderService $service;
    private OrderRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(OrderRepositoryInterface::class);
        $this->service    = new OrderService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_retrieves_order_by_id(): void
    {
        $order               = new Order();
        $order->id           = 42;
        $order->order_number = 'ORD-TEST';

        $this->repository->shouldReceive('findById')->with(42)->once()->andReturn($order);

        $result = $this->service->getOrder(42);

        $this->assertEquals(42, $result->id);
    }

    /** @test */
    public function it_throws_when_order_not_found(): void
    {
        $this->repository->shouldReceive('findById')->with(999)->once()->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->getOrder(999);
    }

    /** @test */
    public function it_cannot_cancel_a_shipped_order(): void
    {
        $order         = new Order();
        $order->id     = 1;
        $order->status = Order::STATUS_SHIPPED;
        $order->saga_state = [];

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());
        $this->repository->shouldReceive('findById')->with(1)->once()->andReturn($order);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage("Cannot cancel order with status 'shipped'");

        $this->service->cancelOrder(1, 'Changed mind');
    }
}
