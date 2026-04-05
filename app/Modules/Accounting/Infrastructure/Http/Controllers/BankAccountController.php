<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Domain\Entities\BankAccount;
use Modules\Accounting\Domain\RepositoryInterfaces\BankAccountRepositoryInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class BankAccountController extends Controller
{
    public function __construct(private readonly BankAccountRepositoryInterface $repo) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->repo->allByTenant($tenantId)->map(fn(BankAccount $b) => $this->serialize($b))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id'          => 'required|uuid',
            'name'               => 'required|string|max:255',
            'account_number'     => 'nullable|string|max:100',
            'bank_name'          => 'nullable|string|max:255',
            'account_type'       => 'required|in:checking,savings,credit_card,line_of_credit,paypal,other',
            'currency'           => 'nullable|string|size:3',
            'chart_of_account_id'=> 'nullable|uuid',
        ]);
        return response()->json($this->serialize($this->repo->create($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        $b = $this->repo->findById($id);
        return $b ? response()->json($this->serialize($b)) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => 'sometimes|string', 'is_active' => 'nullable|boolean']);
        return response()->json($this->serialize($this->repo->update($id, $data)));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);
        return response()->json(null, 204);
    }

    private function serialize(BankAccount $b): array
    {
        return ['id' => $b->getId(), 'name' => $b->getName(), 'account_type' => $b->getAccountType(),
            'balance' => $b->getBalance(), 'currency' => $b->getCurrency(), 'is_active' => $b->isActive()];
    }
}
