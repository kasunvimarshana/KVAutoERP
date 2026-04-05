<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Configuration\Application\Contracts\SettingServiceInterface;
use Modules\Core\Domain\Exceptions\NotFoundException;

class SettingController extends Controller
{
    public function __construct(private readonly SettingServiceInterface $service) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID') ?? $request->user()?->tenant_id;
        $settings = $this->service->getAllByTenant($tenantId);

        return response()->json($settings->map(fn ($s) => $this->serialize($s))->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tenant_id'   => 'required|uuid',
            'key'         => 'required|string|max:255',
            'value'       => 'required|string',
            'type'        => 'nullable|in:string,integer,boolean,json,array',
            'module'      => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $setting = $this->service->set(
            $data['key'],
            $data['value'],
            $data['tenant_id'],
            $data['type'] ?? 'string',
            $data['module'] ?? null,
        );

        return response()->json($this->serialize($setting), 201);
    }

    public function show(string $id): JsonResponse
    {
        try {
            $setting = $this->service->get($id, '');

            return response()->json($this->serialize($setting));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'value'       => 'required|string',
            'type'        => 'nullable|in:string,integer,boolean,json,array',
            'description' => 'nullable|string',
        ]);

        try {
            $setting = $this->service->set($id, $data['value'], '', $data['type'] ?? 'string');

            return response()->json($this->serialize($setting));
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return response()->json(null, 204);
        } catch (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    private function serialize(\Modules\Configuration\Domain\Entities\Setting $s): array
    {
        return [
            'id'          => $s->getId(),
            'tenant_id'   => $s->getTenantId(),
            'key'         => $s->getKey(),
            'value'       => $s->getCastedValue(),
            'type'        => $s->getType(),
            'module'      => $s->getModule(),
            'description' => $s->getDescription(),
        ];
    }
}
