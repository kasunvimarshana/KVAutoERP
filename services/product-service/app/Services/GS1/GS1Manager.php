<?php

namespace App\Services\GS1;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class GS1Manager
{
    /**
     * GS1 Application Identifiers (AI)
     */
    protected const AI_GTIN = '01';
    protected const AI_LOT = '10';
    protected const AI_EXPIRY = '17';
    protected const AI_SERIAL = '21';
    protected const AI_QTY = '30';

    /**
     * Parses a GS1-128 barcode string into its components
     * Example: (01)01234567890123(10)LOT123(17)261231
     *
     * @param string $barcode
     * @return array|bool
     */
    public function parseGS1128(string $barcode): array|bool
    {
        // Simple regex-based GS1 parser for the most common AI codes
        // In a production system, this would use a more robust GS1 library
        $patterns = [
            'gtin' => '/\(01\)(\d{14})/',
            'lot' => '/\(10\)([a-zA-Z0-9]{1,20})/',
            'expiry' => '/\(17\)(\d{6})/',
            'serial' => '/\(21\)([a-zA-Z0-9]{1,20})/',
        ];

        $results = [];
        foreach ($patterns as $key => $pattern) {
            if (preg_match($pattern, $barcode, $matches)) {
                $results[$key] = $matches[1];
            }
        }

        if (empty($results)) {
            Log::warning("GS1 Parsing failed for barcode: {$barcode}");
            return false;
        }

        // Expiry date format YYMMDD to Y-m-d
        if (isset($results['expiry'])) {
            $results['expiry_formatted'] = \DateTime::createFromFormat('ymd', $results['expiry'])->format('Y-m-d');
        }

        return $results;
    }

    /**
     * Validates if a GTIN is correct using the GS1 check digit algorithm
     */
    public function validateGTIN(string $gtin): bool
    {
        if (!preg_match('/^\d{8,14}$/', $gtin)) return false;

        $digits = str_split($gtin);
        $checkDigit = array_pop($digits);
        $sum = 0;
        
        // Reverse for right-to-left calculation
        $digits = array_reverse($digits);
        
        foreach ($digits as $index => $digit) {
            $sum += ($index % 2 === 0) ? $digit * 3 : $digit;
        }

        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;
        return (int)$checkDigit === $calculatedCheckDigit;
    }

    /**
     * Generates a GS1-128 string for a product and lot
     */
    public function generateBarcode(Product $product, string $lotNumber, string $expiryDate): string
    {
        $gtin = str_pad($product->barcode, 14, '0', STR_PAD_LEFT);
        $expiry = date('ymd', strtotime($expiryDate));
        
        return "(01){$gtin}(10){$lotNumber}(17){$expiry}";
    }
}
