<?php

namespace App\Http\Controllers;

use App\Repositories\SagaRepository;
use App\Services\OrderSagaOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SagaController extends Controller
{
    public function __construct(
        private readonly SagaRepository        $sagaRepository,
        private readonly OrderSagaOrchestrator $orchestrator,
    ) {}

    public function getSagaStatus(Request $request, string $sagaId): JsonResponse
    {
        $saga = $this->sagaRepository->find($sagaId);

        if ($saga === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Saga not found.', 'meta' => []], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $saga->id,
                'order_id'     => $saga->order_id,
                'status'       => $saga->status,
                'current_step' => $saga->current_step,
                'steps'        => $saga->steps,
            ],
            'message' => 'Saga retrieved.',
            'meta'    => [],
        ]);
    }

    public function retryFailedSaga(Request $request, string $sagaId): JsonResponse
    {
        $saga = $this->sagaRepository->find($sagaId);

        if ($saga === null) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Saga not found.', 'meta' => []], 404);
        }

        if ($saga->status !== 'failed') {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Only failed sagas can be retried.', 'meta' => []], 422);
        }

        $tenantId  = $request->attributes->get('tenant_id');
        $orderData = $saga->context ?? [];

        try {
            $this->sagaRepository->updateSagaState($sagaId, 'running');
            $order = $this->orchestrator->execute($orderData, $tenantId);

            return response()->json([
                'success' => true,
                'data'    => ['order_id' => $order->id, 'status' => $order->status],
                'message' => 'Saga retried successfully.',
                'meta'    => [],
            ]);
        } catch (\Throwable $e) {
            $this->sagaRepository->updateSagaState($sagaId, 'failed');

            return response()->json(['success' => false, 'data' => null, 'message' => 'Saga retry failed: ' . $e->getMessage(), 'meta' => []], 500);
        }
    }
}
