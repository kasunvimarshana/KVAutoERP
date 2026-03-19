<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\WorkflowDefinitionServiceInterface;
use App\DTOs\WorkflowDefinitionDto;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\CreateWorkflowDefinitionRequest;
use App\Http\Requests\UpdateWorkflowDefinitionRequest;
use App\Http\Resources\WorkflowDefinitionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkflowDefinitionController extends Controller
{
    public function __construct(
        private readonly WorkflowDefinitionServiceInterface $workflowService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->workflowService->listForTenant($tenantId, $perPage);

        return response()->json([
            'success' => true,
            'data'    => WorkflowDefinitionResource::collection($paginator->items()),
            'message' => 'Workflow definitions retrieved successfully.',
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ]);
    }

    public function store(CreateWorkflowDefinitionRequest $request): JsonResponse
    {
        try {
            $dto = WorkflowDefinitionDto::fromArray($request->validated());
            $workflow = $this->workflowService->create($dto);

            return response()->json([
                'success' => true,
                'data'    => new WorkflowDefinitionResource($workflow),
                'message' => 'Workflow definition created successfully.',
            ], 201);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $workflow = $this->workflowService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new WorkflowDefinitionResource($workflow),
                'message' => 'Workflow definition retrieved successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 404);
        }
    }

    public function update(UpdateWorkflowDefinitionRequest $request, string $id): JsonResponse
    {
        try {
            $existing = $this->workflowService->findById($id);
            $dto = WorkflowDefinitionDto::fromArray(
                array_merge($existing->toArray(), $request->validated()),
            );
            $workflow = $this->workflowService->update($id, $dto);

            return response()->json([
                'success' => true,
                'data'    => new WorkflowDefinitionResource($workflow),
                'message' => 'Workflow definition updated successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->workflowService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Workflow definition deleted successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
