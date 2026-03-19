<?php

declare(strict_types=1);

namespace KvEnterprise\SharedKernel\Concerns;

/**
 * Provides a single, canonical UUID v4 generator for use across the platform.
 *
 * Applied to value objects, models, events, and any other class that must
 * produce random UUID v4 strings, eliminating duplicated generation logic.
 */
trait GeneratesUuid
{
    /**
     * Generate a cryptographically random UUID v4 string.
     *
     * Uses PHP's {@see random_bytes()} as the entropy source and sets
     * the version (bits 12–15 of octet 6) and variant bits (bits 6–7
     * of octet 8) per RFC 4122 section 4.4.
     *
     * @return string  A lower-case, hyphen-separated UUID v4 string.
     */
    public static function generateUuidV4(): string
    {
        $data = random_bytes(16);

        // Set version to 4 (0100xxxx in the high nibble of octet 6).
        $data[6] = chr((ord($data[6]) & 0x0F) | 0x40);

        // Set variant bits to 10xxxxxx (RFC 4122) in octet 8.
        $data[8] = chr((ord($data[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
