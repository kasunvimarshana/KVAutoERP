<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;
use Modules\Currency\Application\Contracts\CurrencyServiceInterface;

class CurrencyController extends BaseController
{
    public function __construct(
        private readonly CurrencyServiceInterface $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $currencies = $this->service->listAll($tenantId);

        return response()->json(['data' => array_map(fn ($c) => [
            'id'             => $c->getId(),
            'code'           => $c->getCode(),
            'name'           => $c->getName(),
            'symbol'         => $c->getSymbol(),
            'decimal_places' => $c->getDecimalPlaces(),
            'is_default'     => $c->isDefault(),
            'is_active'      => $c->isActive(),
        ], $currencies)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'      => 'required|integer',
            'code'           => 'required|string|max:10',
            'name'           => 'required|string|max:100',
            'symbol'         => 'required|string|max:10',
            'decimal_places' => 'integer|min:0|max:8',
            'is_default'     => 'boolean',
            'is_active'      => 'boolean',
        ]);

        $currency = $this->service->create($validated);

        return response()->json(['data' => [
            'id'             => $currency->getId(),
            'code'           => $currency->getCode(),
            'name'           => $currency->getName(),
            'symbol'         => $currency->getSymbol(),
            'decimal_places' => $currency->getDecimalPlaces(),
            'is_default'     => $currency->isDefault(),
            'is_active'      => $currency->isActive(),
        ]], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $currency = $this->service->findById($id, $tenantId);

        if ($currency === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json(['data' => [
            'id'             => $currency->getId(),
            'code'           => $currency->getCode(),
            'name'           => $currency->getName(),
            'symbol'         => $currency->getSymbol(),
            'decimal_places' => $currency->getDecimalPlaces(),
            'is_default'     => $currency->isDefault(),
            'is_active'      => $currency->isActive(),
        ]]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'code'           => 'sometimes|string|max:10',
            'name'           => 'sometimes|string|max:100',
            'symbol'         => 'sometimes|string|max:10',
            'decimal_places' => 'sometimes|integer|min:0|max:8',
            'is_default'     => 'sometimes|boolean',
            'is_active'      => 'sometimes|boolean',
        ]);

        $currency = $this->service->update($id, $validated);

        return response()->json(['data' => [
            'id'             => $currency->getId(),
            'code'           => $currency->getCode(),
            'name'           => $currency->getName(),
            'symbol'         => $currency->getSymbol(),
            'decimal_places' => $currency->getDecimalPlaces(),
            'is_default'     => $currency->isDefault(),
            'is_active'      => $currency->isActive(),
        ]]);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $currency = $this->service->setDefault($id, $tenantId);

        return response()->json(['data' => [
            'id'         => $currency->getId(),
            'code'       => $currency->getCode(),
            'is_default' => $currency->isDefault(),
        ]]);
    }
}
