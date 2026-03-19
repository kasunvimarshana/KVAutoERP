<?php

namespace App\Services\SagaSteps;

use App\Repositories\OrderRepository;
use Shared\Core\Contracts\SagaStepInterface;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatusStep implements SagaStepInterface
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Handle the Saga step
     */
    public function handle(array $data): bool|array
    {
        Log::info("Executing UpdateOrderStatusStep to 'Confirmed'");
        $this->orderRepository->update(['status' => 'Confirmed'], $data['order_id']);
        return true;
    }

    /**
     * Rollback the Saga step
     */
    public function rollback(array $data): bool
    {
        Log::warning("Rolling back UpdateOrderStatusStep to 'Pending'");
        if (isset($data['order_id'])) {
            return $this->orderRepository->update(['status' => 'Pending'], $data['order_id']);
        }
        return true;
    }
}
