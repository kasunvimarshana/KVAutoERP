<?php

namespace App\Repositories;

use App\Models\OrderSaga;
use Illuminate\Database\Eloquent\Collection;

class SagaRepository extends BaseRepository
{
    public function __construct(OrderSaga $model)
    {
        parent::__construct($model);
    }

    public function createSaga(string $orderId, array $steps, array $context): OrderSaga
    {
        return $this->create([
            'order_id'     => $orderId,
            'status'       => 'pending',
            'current_step' => $steps[0]['name'] ?? null,
            'steps'        => $steps,
            'context'      => $context,
        ]);
    }

    public function updateSagaState(string $sagaId, string $status, ?string $currentStep = null): ?OrderSaga
    {
        $saga = $this->find($sagaId);

        if ($saga === null) {
            return null;
        }

        $data = ['status' => $status];

        if ($currentStep !== null) {
            $data['current_step'] = $currentStep;
        }

        $saga->fill($data)->save();

        return $saga->fresh();
    }

    public function getByOrderId(string $orderId): ?OrderSaga
    {
        return $this->model->newQuery()
            ->where('order_id', $orderId)
            ->latest()
            ->first();
    }

    public function getFailedSagas(): Collection
    {
        return $this->model->newQuery()
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function recordStep(string $sagaId, string $stepName, string $status, ?string $error = null): void
    {
        $saga = $this->find($sagaId);

        if ($saga === null) {
            return;
        }

        $steps = $saga->steps ?? [];

        foreach ($steps as &$step) {
            if ($step['name'] === $stepName) {
                $step['status']      = $status;
                $step['executed_at'] = now()->toIso8601String();

                if ($error !== null) {
                    $step['error'] = $error;
                }

                break;
            }
        }

        $saga->steps        = $steps;
        $saga->current_step = $stepName;
        $saga->save();
    }
}
