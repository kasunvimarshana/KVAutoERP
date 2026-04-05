<?php

declare(strict_types=1);

namespace Modules\Barcode\Infrastructure\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Barcode\Application\Contracts\BarcodeGeneratorServiceInterface;
use Modules\Barcode\Application\Contracts\BarcodeScannerServiceInterface;
use Modules\Barcode\Domain\Entities\Barcode;
use Modules\Core\Infrastructure\Http\Controllers\BaseController;

class BarcodeController extends BaseController
{
    public function __construct(
        private readonly BarcodeGeneratorServiceInterface $generatorService,
        private readonly BarcodeScannerServiceInterface $scannerService,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbology' => 'required|string|in:'.implode(',', Barcode::VALID_SYMBOLOGIES),
            'data'      => 'required|string|max:500',
            'tenant_id' => 'required|integer',
        ]);

        $barcode = $this->generatorService->generate(
            $validated['symbology'],
            $validated['data'],
            (int) $validated['tenant_id']
        );

        return response()->json(['data' => $this->barcodeToArray($barcode)], 201);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'raw_data'  => 'required|string',
            'tenant_id' => 'required|integer',
        ]);

        $result = $this->scannerService->scan($validated['raw_data'], (int) $validated['tenant_id']);

        return response()->json(['data' => $result]);
    }

    private function barcodeToArray(Barcode $barcode): array
    {
        return [
            'id'           => $barcode->getId(),
            'symbology'    => $barcode->getSymbology(),
            'data'         => $barcode->getData(),
            'check_digit'  => $barcode->getCheckDigit(),
            'encoded_data' => $barcode->getEncodedData(),
            'generated_at' => $barcode->getGeneratedAt()->format(\DateTimeInterface::ATOM),
        ];
    }
}
