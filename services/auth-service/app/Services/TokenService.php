<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\TokenRevocationRepositoryInterface;
use App\Contracts\Services\TokenServiceInterface;
use App\DTOs\TokenClaimsDto;
use App\Exceptions\TokenException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class TokenService implements TokenServiceInterface
{
    private string $algorithm;
    private string $issuer;

    public function __construct(
        private readonly TokenRevocationRepositoryInterface $revocationRepository,
    ) {
        $this->algorithm = config('jwt.algo', 'RS256');
        $this->issuer = config('jwt.issuer', config('app.url'));
    }

    public function issueAccessToken(TokenClaimsDto $claims): string
    {
        $now = time();
        $ttlMinutes = $claims->ttlMinutes ?? config('jwt.ttl.access', 15);
        $jti = $claims->jti ?: Uuid::uuid4()->toString();

        $payload = array_merge($claims->toArray(), [
            'iss' => $this->issuer,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + ($ttlMinutes * 60),
            'jti' => $jti,
        ]);

        $privateKey = $this->getPrivateKey();

        return JWT::encode($payload, $privateKey, $this->algorithm);
    }

    public function issueRefreshToken(string $userId, string $sessionId): string
    {
        return Str::random(64) . '.' . base64_encode($userId . '.' . now()->timestamp);
    }

    public function decodeAccessToken(string $token): array
    {
        try {
            $publicKey = $this->getPublicKey();
            $decoded = JWT::decode($token, new Key($publicKey, $this->algorithm));
            $payload = (array) $decoded;

            // Check token revocation
            $jti = $payload['jti'] ?? null;
            if ($jti && $this->isRevoked($jti)) {
                throw new TokenException('Token has been revoked.', 401);
            }

            return $payload;
        } catch (TokenException $e) {
            throw $e;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new TokenException('Token has expired.', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new TokenException('Token signature is invalid.', 401);
        } catch (\Exception $e) {
            throw new TokenException('Token is invalid: ' . $e->getMessage(), 401);
        }
    }

    public function verifyRefreshToken(string $rawRefreshToken, string $hashedToken): bool
    {
        return hash_equals($hashedToken, $this->hashRefreshToken($rawRefreshToken));
    }

    public function hashRefreshToken(string $rawRefreshToken): string
    {
        return hash('sha256', $rawRefreshToken);
    }

    public function issueServiceToken(string $serviceId, string $tenantId): string
    {
        $now = time();
        $ttl = config('jwt.ttl.service', 5) * 60;

        $payload = [
            'iss'        => $this->issuer,
            'sub'        => $serviceId,
            'service_id' => $serviceId,
            'tenant_id'  => $tenantId,
            'iat'        => $now,
            'nbf'        => $now,
            'exp'        => $now + $ttl,
            'jti'        => Uuid::uuid4()->toString(),
            'type'       => 'service',
        ];

        return JWT::encode($payload, $this->getPrivateKey(), $this->algorithm);
    }

    public function verifyServiceToken(string $token): array
    {
        $payload = $this->decodeAccessToken($token);

        if (($payload['type'] ?? '') !== 'service') {
            throw new TokenException('Not a valid service token.', 401);
        }

        return $payload;
    }

    public function revokeByJti(string $jti, string $userId, int $expiresInSeconds, string $reason = 'logout'): void
    {
        // Store in both database and Redis for fast lookup
        $expiresAt = now()->addSeconds($expiresInSeconds);

        $this->revocationRepository->revoke($jti, $userId, $expiresAt, $reason);

        // Also cache in Redis for O(1) lookup
        Cache::put(
            config('jwt.revocation.prefix') . $jti,
            ['revoked_at' => now()->toIso8601String(), 'reason' => $reason],
            $expiresInSeconds,
        );
    }

    public function isRevoked(string $jti): bool
    {
        $cacheKey = config('jwt.revocation.prefix') . $jti;

        // Fast Redis lookup first
        if (Cache::has($cacheKey)) {
            return true;
        }

        // Fallback to database
        $isRevoked = $this->revocationRepository->isRevoked($jti);

        if ($isRevoked) {
            // Warm the cache to prevent repeated DB lookups
            Cache::put($cacheKey, true, 300);
        }

        return $isRevoked;
    }

    public function getRemainingTtl(array $payload): int
    {
        $exp = $payload['exp'] ?? 0;
        return max(0, $exp - time());
    }

    public function issueSignedUrlToken(string $url, string $userId, int $ttlSeconds): string
    {
        $now = time();
        $payload = [
            'iss'     => $this->issuer,
            'sub'     => $userId,
            'url'     => $url,
            'url_hash' => hash('sha256', $url),
            'iat'     => $now,
            'exp'     => $now + $ttlSeconds,
            'jti'     => Uuid::uuid4()->toString(),
            'type'    => 'signed_url',
        ];

        return JWT::encode($payload, $this->getPrivateKey(), $this->algorithm);
    }

    public function verifySignedUrlToken(string $token, string $url): bool
    {
        try {
            $payload = $this->decodeAccessToken($token);

            if (($payload['type'] ?? '') !== 'signed_url') {
                return false;
            }

            if (($payload['url_hash'] ?? '') !== hash('sha256', $url)) {
                return false;
            }

            return true;
        } catch (TokenException) {
            return false;
        }
    }

    // -----------------------------------------------------------------
    // Private Helpers
    // -----------------------------------------------------------------

    private function getPrivateKey(): string
    {
        $path = base_path(config('jwt.keys.private'));

        if (! file_exists($path)) {
            throw new TokenException('JWT private key not found. Run: php artisan auth:generate-keys', 500);
        }

        $key = file_get_contents($path);
        if ($key === false) {
            throw new TokenException('Unable to read JWT private key.', 500);
        }

        return $key;
    }

    private function getPublicKey(): string
    {
        $path = base_path(config('jwt.keys.public'));

        if (! file_exists($path)) {
            throw new TokenException('JWT public key not found. Run: php artisan auth:generate-keys', 500);
        }

        $key = file_get_contents($path);
        if ($key === false) {
            throw new TokenException('Unable to read JWT public key.', 500);
        }

        return $key;
    }
}
