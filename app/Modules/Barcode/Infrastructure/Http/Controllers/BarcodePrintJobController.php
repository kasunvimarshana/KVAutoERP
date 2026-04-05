<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Barcode\Application\Contracts\BarcodePrinterDispatcherInterface;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodePrintJobRepositoryInterface;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;

class BarcodePrintJobController extends BaseController
{
    public function __construct(
        private readonly BarcodePrintJobRepositoryInterface $repository,
        private readonly BarcodePrinterDispatcherInterface $dispatcher,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $jobs     = $this->repository->listAll($tenantId);

        return response()->json(['data' => array_map(fn ($j) => $this->jobToArray($j), $jobs)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'         => 'required|integer',
            'label_template_id' => 'nullable|integer',
            'barcode_id'        => 'nullable|integer',
            'printer_id'        => 'nullable|string|max:100',
            'copies'            => 'integer|min:1',
        ]);

        $job = $this->repository->create(array_merge($validated, ['status' => 'pending']));

        return response()->json(['data' => $this->jobToArray($job)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $job      = $this->repository->findById($id, $tenantId);

        if ($job === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['data' => $this->jobToArray($job)]);
    }

    public function dispatch(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $job      = $this->repository->findById($id, $tenantId);

        if ($job === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $success = $this->dispatcher->dispatch($job);

        if ($success) {
            $job->markCompleted();
            $this->repository->update($id, [
                'status'     => $job->getStatus(),
                'printed_at' => $job->getPrintedAt(),
            ]);
        } else {
            $job->markFailed('Dispatch failed.');
            $this->repository->update($id, [
                'status'        => $job->getStatus(),
                'error_message' => $job->getErrorMessage(),
            ]);
        }

        return response()->json(['data' => $this->jobToArray($job)]);
    }

    private function jobToArray(mixed $j): array
    {
        return [
            'id'                 => $j->getId(),
            'label_template_id'  => $j->getLabelTemplateId(),
            'barcode_id'         => $j->getBarcodeId(),
            'status'             => $j->getStatus(),
            'printer_id'         => $j->getPrinterId(),
            'copies'             => $j->getCopies(),
            'printed_at'         => $j->getPrintedAt()?->format(\DateTimeInterface::ATOM),
            'error_message'      => $j->getErrorMessage(),
        ];
    }
}
