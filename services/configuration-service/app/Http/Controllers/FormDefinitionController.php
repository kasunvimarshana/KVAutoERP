<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\FormDefinitionServiceInterface;
use App\DTOs\FormDefinitionDto;
use App\Exceptions\ConfigurationException;
use App\Http\Requests\CreateFormDefinitionRequest;
use App\Http\Requests\UpdateFormDefinitionRequest;
use App\Http\Resources\FormDefinitionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FormDefinitionController extends Controller
{
    public function __construct(
        private readonly FormDefinitionServiceInterface $formService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->attributes->get('tenant_id') ?? $request->query('tenant_id', '');
        $perPage = (int) $request->query('per_page', 15);

        $paginator = $this->formService->listForTenant($tenantId, $perPage);

        return response()->json([
            'success' => true,
            'data'    => FormDefinitionResource::collection($paginator->items()),
            'message' => 'Form definitions retrieved successfully.',
            'meta'    => [
                'page'     => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total'    => $paginator->total(),
            ],
        ]);
    }

    public function store(CreateFormDefinitionRequest $request): JsonResponse
    {
        try {
            $dto = FormDefinitionDto::fromArray($request->validated());
            $form = $this->formService->create($dto);

            return response()->json([
                'success' => true,
                'data'    => new FormDefinitionResource($form),
                'message' => 'Form definition created successfully.',
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
            $form = $this->formService->findById($id);

            return response()->json([
                'success' => true,
                'data'    => new FormDefinitionResource($form),
                'message' => 'Form definition retrieved successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 404);
        }
    }

    public function update(UpdateFormDefinitionRequest $request, string $id): JsonResponse
    {
        try {
            $existing = $this->formService->findById($id);
            $dto = FormDefinitionDto::fromArray(
                array_merge($existing->toArray(), $request->validated()),
            );
            $form = $this->formService->update($id, $dto);

            return response()->json([
                'success' => true,
                'data'    => new FormDefinitionResource($form),
                'message' => 'Form definition updated successfully.',
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
            $this->formService->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Form definition deleted successfully.',
            ]);
        } catch (ConfigurationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 400);
        }
    }
}
