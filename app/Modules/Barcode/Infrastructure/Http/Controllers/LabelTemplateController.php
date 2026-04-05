<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Barcode\Application\Contracts\LabelTemplateServiceInterface;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;

class LabelTemplateController extends BaseController
{
    public function __construct(
        private readonly LabelTemplateServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId  = (int) $request->header('X-Tenant-ID', 0);
        $templates = $this->service->findById(0, $tenantId) ? [] : [];

        return response()->json(['data' => $templates]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|integer',
            'name'      => 'required|string|max:255',
            'format'    => 'required|in:zpl,epl,svg',
            'template'  => 'required|string',
            'width'     => 'nullable|numeric',
            'height'    => 'nullable|numeric',
            'variables' => 'nullable|array',
        ]);

        $template = $this->service->create($validated);

        return response()->json(['data' => $this->templateToArray($template)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $template = $this->service->findById($id, $tenantId);

        if ($template === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['data' => $this->templateToArray($template)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name'      => 'sometimes|string|max:255',
            'format'    => 'sometimes|in:zpl,epl,svg',
            'template'  => 'sometimes|string',
            'width'     => 'nullable|numeric',
            'height'    => 'nullable|numeric',
            'variables' => 'nullable|array',
        ]);

        $template = $this->service->update($id, $validated);

        return response()->json(['data' => $this->templateToArray($template)]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);

        return response()->json(null, 204);
    }

    public function render(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'data'      => 'required|array',
            'tenant_id' => 'required|integer',
        ]);

        $rendered = $this->service->render($id, $validated['data'], (int) $validated['tenant_id']);

        return response()->json(['data' => ['rendered' => $rendered]]);
    }

    private function templateToArray(mixed $t): array
    {
        return [
            'id'        => $t->getId(),
            'name'      => $t->getName(),
            'format'    => $t->getFormat(),
            'width'     => $t->getWidth(),
            'height'    => $t->getHeight(),
            'variables' => $t->getVariables(),
        ];
    }
}
