<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\BulkReclassifyTransactionsServiceInterface;
use Modules\Accounting\Application\Contracts\CategorizeTransactionServiceInterface;
use Modules\Accounting\Application\Contracts\ImportBankTransactionsServiceInterface;
use Modules\Accounting\Domain\Entities\BankTransaction;
use Modules\Accounting\Domain\RepositoryInterfaces\BankTransactionRepositoryInterface;

class BankTransactionController extends Controller
{
    public function __construct(
        private readonly BankTransactionRepositoryInterface $repo,
        private readonly ImportBankTransactionsServiceInterface $importService,
        private readonly CategorizeTransactionServiceInterface $categorizeService,
        private readonly BulkReclassifyTransactionsServiceInterface $reclassifyService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->repo->allByTenant($tenantId)->map(fn(BankTransaction $t) => $this->serialize($t))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id'       => 'required|uuid',
            'bank_account_id' => 'required|uuid',
            'date'            => 'required|date',
            'description'     => 'required|string',
            'amount'          => 'required|numeric',
            'type'            => 'required|in:debit,credit',
        ]);
        return response()->json($this->serialize($this->repo->create($data)), 201);
    }

    public function show(string $id): JsonResponse
    {
        $t = $this->repo->findById($id);
        return $t ? response()->json($this->serialize($t)) : response()->json(['message' => 'Not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['description' => 'sometimes|string', 'status' => 'nullable|in:pending,categorized,reconciled,excluded']);
        return response()->json($this->serialize($this->repo->update($id, $data)));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->repo->delete($id);
        return response()->json(null, 204);
    }

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bank_account_id' => 'required|uuid',
            'transactions'    => 'required|array|min:1',
        ]);
        $count = $this->importService->import($data['bank_account_id'], $data['transactions']);
        return response()->json(['imported' => $count]);
    }

    public function bulkReclassify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'transaction_ids' => 'required|array|min:1',
            'category_id'     => 'required|uuid',
        ]);
        $count = $this->reclassifyService->reclassify($data['transaction_ids'], $data['category_id']);
        return response()->json(['updated' => $count]);
    }

    private function serialize(BankTransaction $t): array
    {
        return ['id' => $t->getId(), 'bank_account_id' => $t->getBankAccountId(),
            'date' => $t->getDate()->format('Y-m-d'), 'description' => $t->getDescription(),
            'amount' => $t->getAmount(), 'type' => $t->getType(), 'status' => $t->getStatus()];
    }
}
