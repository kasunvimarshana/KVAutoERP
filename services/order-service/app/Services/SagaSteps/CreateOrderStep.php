<?php

namespace App\Services\SagaSteps;

use App\Repositories\OrderRepository;
use Shared\Core\Contracts\SagaStepInterface;
use Illuminate\Support\Facades\Log;

class CreateOrderStep implements SagaStepInterface
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
     *
     * @param array $data
     * @return array|bool
     */
    public function handle(array $data): bool|array
    {
        Log::info("Executing CreateOrderStep");
        $order = $this->orderRepository->create($data);
        return ['order_id' => $order->id, 'order' => $order];
    }

    /**
     * Rollback the Saga step
     *
     * @param array $data
     * @return bool
     */
    public function rollback(array $data): bool
    {
        Log::warning("Rolling back CreateOrderStep");
        if (isset($data['order_id'])) {
            return $this->orderRepository->delete($data['order_id']);
        }
        return true;
    }
}
