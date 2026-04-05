<?php

declare(strict_types=1);

namespace Modules\Barcode\Application\Services;

use Modules\Barcode\Application\Contracts\BarcodeGeneratorServiceInterface;
use Modules\Barcode\Domain\Entities\Barcode;
use Modules\Barcode\Domain\RepositoryInterfaces\BarcodeRepositoryInterface;
use Modules\Core\Domain\Exceptions\DomainException;

class BarcodeGeneratorService implements BarcodeGeneratorServiceInterface
{
    public function __construct(
        private readonly BarcodeRepositoryInterface $repository,
    ) {}

    public function generate(string $symbology, string $data, int $tenantId): Barcode
    {
        if (! in_array($symbology, Barcode::VALID_SYMBOLOGIES, true)) {
            throw new DomainException("Unsupported barcode symbology: {$symbology}");
        }

        [$checkDigit, $encodedData] = match ($symbology) {
            'ean13'           => $this->encodeEan13($data),
            'ean8'            => $this->encodeEan8($data),
            'upc_a'           => $this->encodeUpcA($data),
            'code128'         => $this->encodeCode128($data),
            'qr_code'         => $this->encodeQr($data),
            default           => [null, base64_encode($data)],
        };

        return $this->repository->create([
            'tenant_id'    => $tenantId,
            'symbology'    => $symbology,
            'data'         => $data,
            'check_digit'  => $checkDigit,
            'encoded_data' => $encodedData,
            'generated_at' => now(),
            'metadata'     => [],
        ]);
    }

    /** @return array{string, string} [checkDigit, encodedData] */
    private function encodeEan13(string $data): array
    {
        $digits = preg_replace('/\D/', '', $data);

        if ($digits === null || (strlen($digits) !== 12 && strlen($digits) !== 13)) {
            throw new DomainException('EAN-13 requires 12 or 13 numeric digits.');
        }

        if (strlen($digits) === 13) {
            $digits = substr($digits, 0, 12);
        }

        $checkDigit = $this->ean13CheckDigit($digits);

        return [$checkDigit, $digits.$checkDigit];
    }

    private function ean13CheckDigit(string $digits): string
    {
        $sum = 0;

        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }

    /** @return array{string, string} */
    private function encodeEan8(string $data): array
    {
        $digits = preg_replace('/\D/', '', $data);

        if ($digits === null || (strlen($digits) !== 7 && strlen($digits) !== 8)) {
            throw new DomainException('EAN-8 requires 7 or 8 numeric digits.');
        }

        if (strlen($digits) === 8) {
            $digits = substr($digits, 0, 7);
        }

        $sum = 0;

        for ($i = 0; $i < 7; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 === 0 ? 3 : 1);
        }

        $checkDigit = (string) ((10 - ($sum % 10)) % 10);

        return [$checkDigit, $digits.$checkDigit];
    }

    /** @return array{string, string} */
    private function encodeUpcA(string $data): array
    {
        $digits = preg_replace('/\D/', '', $data);

        if ($digits === null || (strlen($digits) !== 11 && strlen($digits) !== 12)) {
            throw new DomainException('UPC-A requires 11 or 12 numeric digits.');
        }

        if (strlen($digits) === 12) {
            $digits = substr($digits, 0, 11);
        }

        $sum = 0;

        for ($i = 0; $i < 11; $i++) {
            $sum += (int) $digits[$i] * ($i % 2 === 0 ? 3 : 1);
        }

        $checkDigit = (string) ((10 - ($sum % 10)) % 10);

        return [$checkDigit, $digits.$checkDigit];
    }

    /** @return array{null, string} */
    private function encodeCode128(string $data): array
    {
        // Code 128 encoded as base64 representation for storage
        $encoded = 'C128:'.base64_encode($data);

        return [null, $encoded];
    }

    /** @return array{null, string} */
    private function encodeQr(string $data): array
    {
        // QR code matrix notation for storage (actual rendering done client-side or via library)
        $encoded = 'QR:'.base64_encode($data);

        return [null, $encoded];
    }
}
