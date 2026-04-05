<?php

declare(strict_types=1);

namespace Modules\Accounting\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Accounting\Application\Contracts\JournalEntryServiceInterface;
use Modules\Accounting\Domain\Entities\JournalEntry;
use Modules\Core\Domain\Exceptions\DomainException;
use Modules\Core\Domain\Exceptions\NotFoundException;

class JournalEntryController extends Controller
{
    public function __construct(private readonly JournalEntryServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        return response()->json($this->service->getAll($tenantId)->map(fn(JournalEntry $e) => $this->serialize($e))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id'   => 'required|uuid',
            'date'        => 'required|date',
            'description' => 'required|string',
            'reference'   => 'nullable|string',
            'created_by'  => 'nullable|uuid',
            'lines'       => 'required|array|min:2',
            'lines.*.account_id'  => 'required|uuid',
            'lines.*.debit'       => 'nullable|numeric|min:0',
            'lines.*.credit'      => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        try {
            $lines = $data['lines'];
            unset($data['lines']);
            return response()->json($this->serialize($this->service->createEntry($data, $lines)), 201);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->service->getEntry($id)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['description' => 'sometimes|string', 'reference' => 'nullable|string']);
        try {
            return response()->json($this->serialize($this->service->getEntry($id)));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->getEntry($id);
            return response()->json(null, 204);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function post(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->service->postEntry($id)));
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function void(string $id): JsonResponse
    {
        try {
            return response()->json($this->serialize($this->service->voidEntry($id)));
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function serialize(JournalEntry $e): array
    {
        return [
            'id'           => $e->getId(),
            'tenant_id'    => $e->getTenantId(),
            'entry_number' => $e->getEntryNumber(),
            'date'         => $e->getDate()->format('Y-m-d'),
            'description'  => $e->getDescription(),
            'reference'    => $e->getReference(),
            'status'       => $e->getStatus(),
            'total_debit'  => $e->getTotalDebit(),
            'total_credit' => $e->getTotalCredit(),
            'is_balanced'  => $e->isBalanced(),
        ];
    }
}
