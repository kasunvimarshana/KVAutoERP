<?php

declare(strict_types=1);

namespace Modules\Currency\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;
use Modules\Currency\Application\Contracts\ConvertAmountServiceInterface;
use Modules\Currency\Application\Contracts\ExchangeRateServiceInterface;

class ExchangeRateController extends BaseController
{
    public function __construct(
        private readonly ExchangeRateServiceInterface $service,
        private readonly ConvertAmountServiceInterface $convertService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->header('X-Tenant-ID', 0);
        $from = (string) $request->query('from', '');
        $to = (string) $request->query('to', '');

        $rates = $this->service->listForPair($from, $to, $tenantId);

        return response()->json(['data' => array_map(fn ($r) => [
            'id'             => $r->getId(),
            'from_currency'  => $r->getFromCurrency(),
            'to_currency'    => $r->getToCurrency(),
            'rate'           => $r->getRate(),
            'effective_date' => $r->getEffectiveDate()->format('Y-m-d'),
            'source'         => $r->getSource(),
        ], $rates)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id'      => 'required|integer',
            'from_currency'  => 'required|string|max:10',
            'to_currency'    => 'required|string|max:10',
            'rate'           => 'required|numeric|min:0.000001',
            'effective_date' => 'required|date',
            'source'         => 'in:manual,api',
        ]);

        $rate = $this->service->create($validated);

        return response()->json(['data' => [
            'id'             => $rate->getId(),
            'from_currency'  => $rate->getFromCurrency(),
            'to_currency'    => $rate->getToCurrency(),
            'rate'           => $rate->getRate(),
            'effective_date' => $rate->getEffectiveDate()->format('Y-m-d'),
            'source'         => $rate->getSource(),
        ]], 201);
    }

    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'    => 'required|numeric',
            'from'      => 'required|string|max:10',
            'to'        => 'required|string|max:10',
            'tenant_id' => 'required|integer',
            'date'      => 'nullable|date',
        ]);

        $date = isset($validated['date']) ? new \DateTime($validated['date']) : null;
        $result = $this->convertService->convert(
            (float) $validated['amount'],
            $validated['from'],
            $validated['to'],
            (int) $validated['tenant_id'],
            $date
        );

        return response()->json(['data' => ['converted_amount' => $result]]);
    }
}
