<?php

declare(strict_types=1);

namespace KvEnterprise\SharedAuth\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use KvEnterprise\SharedAuth\Contracts\JwtVerifierInterface;
use KvEnterprise\SharedAuth\Exceptions\TokenVerificationException;

/**
 * Verifies JWT tokens locally using the Auth service's public key.
 * Designed to be used by ALL microservices — never calls the Auth service
 * for each request, ensuring fault tolerance and horizontal scalability.
 *
 * Public key distribution strategies supported:
 *  1. Local file (default): public key file shipped alongside the service
 *  2. Remote URL: fetched and cached from the Auth service's JWKS endpoint
 *  3. Environment variable: PEM-encoded key in APP_JWT_PUBLIC_KEY
 */
class JwtVerifier implements JwtVerifierInterface
{
    private string $algorithm;
    private string $revocationPrefix;

    public function __construct()
    {
        $this->algorithm = config('shared_auth.jwt_algo', 'RS256');
        $this->revocationPrefix = config('shared_auth.revocation_prefix', 'kv_auth_revoked:');
    }

    public function verify(string $token): array
    {
        try {
            $publicKey = $this->getPublicKey();
            $decoded = JWT::decode($token, new Key($publicKey, $this->algorithm));
            $payload = (array) $decoded;

            // Validate required claims
            $this->validateRequiredClaims($payload);

            // Check revocation list
            $jti = $payload['jti'] ?? null;
            if ($jti && $this->isRevoked($jti)) {
                throw new TokenVerificationException('Token has been revoked.', 401);
            }

            return $payload;
        } catch (TokenVerificationException $e) {
            throw $e;
        } catch (\Firebase\JWT\ExpiredException) {
            throw new TokenVerificationException('Token has expired.', 401);
        } catch (\Firebase\JWT\SignatureInvalidException) {
            throw new TokenVerificationException('Token signature is invalid.', 401);
        } catch (\Exception $e) {
            throw new TokenVerificationException('Token is invalid: ' . $e->getMessage(), 401);
        }
    }

    public function isRevoked(string $jti): bool
    {
        // Fast O(1) lookup in Redis
        return Cache::has($this->revocationPrefix . $jti);
    }

    public function getRemainingTtl(array $payload): int
    {
        $exp = $payload['exp'] ?? 0;
        return max(0, (int) $exp - time());
    }

    private function validateRequiredClaims(array $payload): void
    {
        $required = ['sub', 'tenant_id', 'jti', 'exp', 'iss'];

        foreach ($required as $claim) {
            if (empty($payload[$claim])) {
                throw new TokenVerificationException("Token is missing required claim: {$claim}", 401);
            }
        }
    }

    private function getPublicKey(): string
    {
        // 1. Check environment variable (highest priority)
        $envKey = config('shared_auth.jwt_public_key');
        if (! empty($envKey)) {
            return $envKey;
        }

        // 2. Check local file
        $keyPath = config('shared_auth.jwt_public_key_path');
        if ($keyPath && file_exists($keyPath)) {
            $key = file_get_contents($keyPath);
            if ($key !== false) {
                return $key;
            }
        }

        // 3. Fetch from Auth service JWKS endpoint (with caching)
        $jwksUrl = config('shared_auth.auth_service_jwks_url');
        if ($jwksUrl) {
            return $this->fetchPublicKeyFromJwks($jwksUrl);
        }

        throw new TokenVerificationException(
            'JWT public key not configured. Set SHARED_AUTH_JWT_PUBLIC_KEY_PATH or SHARED_AUTH_JWT_PUBLIC_KEY.',
            500,
        );
    }

    private function fetchPublicKeyFromJwks(string $url): string
    {
        return Cache::remember('shared_auth:public_key', 3600, function () use ($url) {
            $response = Http::timeout(5)->retry(2, 500)->get($url);

            if ($response->failed()) {
                throw new TokenVerificationException(
                    'Failed to fetch public key from Auth service. Status: ' . $response->status(),
                    500,
                );
            }

            $data = $response->json();
            return $data['public_key'] ?? throw new TokenVerificationException('Invalid JWKS response.', 500);
        });
    }
}
