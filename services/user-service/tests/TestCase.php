<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /**
     * Build a minimal JWT token (unsigned) for middleware bypass in tests.
     * Only the payload claims are used by VerifyServiceToken.
     */
    protected function makeServiceToken(
        string $tenantId,
        string $userId,
        array $extraClaims = [],
    ): string {
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

        $payload = base64_encode(json_encode(array_merge([
            'sub'       => $userId,
            'user_id'   => $userId,
            'tenant_id' => $tenantId,
            'exp'       => time() + 3600,
            'iat'       => time(),
        ], $extraClaims)));

        return "{$header}.{$payload}.fake-signature";
    }

    /**
     * Return Authorization headers with a test service token.
     */
    protected function serviceTokenHeaders(string $tenantId, string $userId): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->makeServiceToken($tenantId, $userId),
            'Accept'        => 'application/json',
        ];
    }
}
