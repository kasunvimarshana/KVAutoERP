<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\PaymentServiceInterface;
use Modules\Accounting\Domain\Entities\Payment;
use Modules\Core\Domain\Exceptions\NotFoundException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->service->getAll($tenantId)->map(fn(Payment $p) => $this->serialize($p))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => 'required|uuid', 'payment_date' => 'required|date', 'amount' => 'required|numeric|min:0.01', 'currency' => 'nullable|string|size:3', 'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,other', 'from_account_id' => 'nullable|uuid', 'to_account_id' => 'nullable|uuid', 'reference' => 'nullable|string', 'notes' => 'nullable|string']);
        return response()->json($this->serialize($this->service->createPayment($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        try { return response()->json($this->serialize($this->service->getPayment($id))); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['status' => 'sometimes|string', 'notes' => 'nullable|string']);
        try { return response()->json($this->serialize($this->service->updatePayment($id, $data))); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function destroy(string $id): JsonResponse
    {
        try { $this->service->deletePayment($id); return response()->json(null, 204); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    private function serialize(Payment $p): array
    {
        return ['id' => $p->getId(), 'payment_number' => $p->getPaymentNumber(), 'payment_date' => $p->getPaymentDate()->format('Y-m-d'), 'amount' => $p->getAmount(), 'currency' => $p->getCurrency(), 'payment_method' => $p->getPaymentMethod(), 'status' => $p->getStatus()];
    }
}
