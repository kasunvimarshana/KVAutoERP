<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\RefundServiceInterface;
use Modules\Accounting\Domain\Entities\Refund;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class RefundController extends Controller
{
    public function __construct(private readonly RefundServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->service->getAll($tenantId)->map(fn(Refund $r) => $this->serialize($r))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['tenant_id' => 'required|uuid', 'refund_date' => 'required|date', 'amount' => 'required|numeric|min:0.01', 'currency' => 'nullable|string|size:3', 'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,other', 'account_id' => 'nullable|uuid', 'reference' => 'nullable|string', 'notes' => 'nullable|string', 'original_payment_id' => 'nullable|uuid']);
        try { return response()->json($this->serialize($this->service->createRefund($data)), 201); }
        catch (DomainException $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function show(string $id): JsonResponse
    {
        try { return response()->json($this->serialize($this->service->getRefund($id))); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    public function destroy(string $id): JsonResponse
    {
        try { $this->service->getRefund($id); return response()->json(null, 204); }
        catch (NotFoundException $e) { return response()->json(['message' => $e->getMessage()], 404); }
    }

    private function serialize(Refund $r): array
    {
        return ['id' => $r->getId(), 'refund_number' => $r->getRefundNumber(), 'refund_date' => $r->getRefundDate()->format('Y-m-d'), 'amount' => $r->getAmount(), 'currency' => $r->getCurrency(), 'status' => $r->getStatus()];
    }
}
