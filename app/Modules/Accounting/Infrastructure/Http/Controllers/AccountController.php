<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\AccountServiceInterface;
use Modules\Accounting\Domain\Entities\Account;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class AccountController extends Controller
{
    public function __construct(private readonly AccountServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->service->getAll($tenantId)->map(fn(Account $a) => $this->serialize($a))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id'       => 'required|uuid',
            'code'            => 'required|string|max:50',
            'name'            => 'required|string|max:255',
            'type'            => 'required|in:asset,liability,equity,income,expense,accounts_payable,accounts_receivable,bank,credit_card',
            'parent_id'       => 'nullable|uuid',
            'is_active'       => 'nullable|boolean',
            'opening_balance' => 'nullable|numeric',
            'currency'        => 'nullable|string|size:3',
            'description'     => 'nullable|string',
        ]);

        try {
            return response()->json($this->serialize($this->service->createAccount($data)), 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->service->getAccount($id)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'is_active'   => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        try {
            return response()->json($this->serialize($this->service->updateAccount($id, $data)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->getAccount($id);
            return response()->json(null, 204);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function serialize(Account $a): array
    {
        return [
            'id'              => $a->getId(),
            'tenant_id'       => $a->getTenantId(),
            'code'            => $a->getCode(),
            'name'            => $a->getName(),
            'type'            => $a->getType(),
            'parent_id'       => $a->getParentId(),
            'is_active'       => $a->isActive(),
            'opening_balance' => $a->getOpeningBalance(),
            'current_balance' => $a->getCurrentBalance(),
            'currency'        => $a->getCurrency(),
            'description'     => $a->getDescription(),
        ];
    }
}
