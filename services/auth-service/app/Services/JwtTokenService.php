<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\RevocationServiceInterface;
use App\Exceptions\InvalidTokenException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha384;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use KvEnterprise\SharedKernel\Contracts\Auth\TokenServiceInterface;
use Ramsey\Uuid\Uuid;

/**
 * JWT token service using lcobucci/jwt v5 with RSA asymmetric keys.
 *
 * Supports RS256 (default), RS384, and RS512 for cryptographic agility.
 * Access tokens are short-lived (900 s); refresh-token rotation is
 * orchestrated by AuthService — this service only issues/verifies JWTs.
 */
final class JwtTokenService implements TokenServiceInterface
{
    private readonly Configuration $jwtConfig;
    private readonly string $algorithm;
    private readonly string $issuer;
    private readonly int $accessTokenTtl;

    public function __construct(
        private readonly RevocationServiceInterface $revocationService,
    ) {
        $this->algorithm      = (string) config('jwt.algorithm', 'RS256');
        $this->issuer         = (string) config('jwt.issuer', 'https://auth.kv-enterprise.io');
        $this->accessTokenTtl = (int)    config('jwt.access_token_ttl', 900);
        $this->jwtConfig      = $this->buildConfiguration();
    }

    /**
     * {@inheritDoc}
     *
     * Issues a signed JWT access token embedding all required ERP claims.
     *
     * @param  array<string, mixed>  $claims
     * @param  int|null              $ttl    Override TTL in seconds.
     * @return string
     */
    public function issue(array $claims, ?int $ttl = null): string
    {
        $ttl  = $ttl ?? $this->accessTokenTtl;
        $now  = new \DateTimeImmutable();
        $jti  = Uuid::uuid4()->toString();

        $builder = $this->jwtConfig->builder()
            ->issuedBy($this->issuer)
            ->identifiedBy($jti)
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify("+{$ttl} seconds"));

        // Embed all standard ERP tenant-aware claims.
        foreach ([
            'user_id', 'tenant_id', 'organization_id', 'branch_id',
            'roles', 'permissions', 'device_id', 'token_version',
        ] as $claim) {
            if (array_key_exists($claim, $claims)) {
                $builder = $builder->withClaim($claim, $claims[$claim]);
            }
        }

        // Pass through any additional custom claims.
        foreach ($claims as $key => $value) {
            if (!in_array($key, [
                'user_id', 'tenant_id', 'organization_id', 'branch_id',
                'roles', 'permissions', 'device_id', 'token_version',
            ], true)) {
                $builder = $builder->withClaim($key, $value);
            }
        }

        return $builder
            ->getToken(
                $this->jwtConfig->signer(),
                $this->jwtConfig->signingKey(),
            )
            ->toString();
    }

    /**
     * {@inheritDoc}
     *
     * Performs full local verification: signature + expiry + revocation.
     */
    public function verify(string $token): bool
    {
        try {
            $parsed = $this->jwtConfig->parser()->parse($token);

            if (!($parsed instanceof Plain)) {
                return false;
            }

            $constraints = $this->jwtConfig->validationConstraints();
            $this->jwtConfig->validator()->assert($parsed, ...$constraints);

            $jti     = $parsed->claims()->get('jti');
            $userId  = $parsed->claims()->get('user_id');
            $version = $parsed->claims()->get('token_version');

            // 1. Check JTI revocation list.
            if ($jti && $this->revocationService->isJtiRevoked((string) $jti)) {
                return false;
            }

            // 2. Check user-level token version (global logout).
            if ($userId !== null && $version !== null) {
                $currentVersion = $this->revocationService->getUserTokenVersion((string) $userId);
                if ((int) $version < $currentVersion) {
                    return false;
                }
            }

            // 3. Check device-level revocation.
            $deviceId = $parsed->claims()->get('device_id');
            if ($userId !== null && $deviceId !== null) {
                if ($this->revocationService->isDeviceRevoked((string) $userId, (string) $deviceId)) {
                    return false;
                }
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * Delegates refresh logic to AuthService; this method exists to satisfy
     * the TokenServiceInterface contract for cross-service use. When called
     * directly it throws — callers must use AuthService::refreshTokens().
     *
     * @throws \LogicException
     */
    public function refresh(string $refreshToken): array
    {
        throw new \LogicException(
            'Use AuthService::refreshTokens() for token rotation. '
            . 'JwtTokenService::refresh() is not supported directly.',
        );
    }

    /**
     * {@inheritDoc}
     *
     * Adds the token's JTI to the Redis revocation list with its remaining TTL.
     */
    public function revoke(string $token): bool
    {
        try {
            $claims = $this->decode($token);
            $jti    = $claims['jti'] ?? null;
            $exp    = $claims['exp'] ?? null;

            if ($jti === null) {
                return false;
            }

            $remainingTtl = $exp !== null
                ? max(1, (int) $exp - time())
                : $this->accessTokenTtl;

            return $this->revocationService->revokeJti((string) $jti, $remainingTtl);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAllForUser(string $userId): bool
    {
        $this->revocationService->revokeAllForUser($userId);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeForDevice(string $userId, string $deviceId): bool
    {
        return $this->revocationService->revokeForDevice($userId, $deviceId);
    }

    /**
     * {@inheritDoc}
     *
     * Decodes claims without verifying the signature. Use only for logging.
     *
     * @return array<string, mixed>
     */
    public function decode(string $token): array
    {
        try {
            $parsed = $this->jwtConfig->parser()->parse($token);

            if (!($parsed instanceof Plain)) {
                return [];
            }

            /** @var array<string, mixed> $claims */
            $claims = $parsed->claims()->all();

            // Normalise DateTimeImmutable values to Unix timestamps.
            foreach ($claims as $key => $value) {
                if ($value instanceof \DateTimeImmutable) {
                    $claims[$key] = $value->getTimestamp();
                }
            }

            return $claims;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isRevoked(string $jti): bool
    {
        return $this->revocationService->isJtiRevoked($jti);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the lcobucci/jwt Configuration based on the configured algorithm.
     *
     * @return Configuration
     */
    private function buildConfiguration(): Configuration
    {
        $signer     = $this->resolveSigner();
        $privateKey = $this->loadPrivateKey();
        $publicKey  = $this->loadPublicKey();

        $config = Configuration::forAsymmetricSigner(
            $signer,
            $privateKey,
            $publicKey,
        );

        $clock = new SystemClock(new \DateTimeZone('UTC'));

        $config->setValidationConstraints(
            new SignedWith($signer, $publicKey),
            new LooseValidAt($clock),
            new IssuedBy($this->issuer),
        );

        return $config;
    }

    /**
     * Resolve the RSA signer from the configured algorithm name.
     *
     * @return \Lcobucci\JWT\Signer
     * @throws \InvalidArgumentException
     */
    private function resolveSigner(): \Lcobucci\JWT\Signer
    {
        return match ($this->algorithm) {
            'RS256' => new Sha256(),
            'RS384' => new Sha384(),
            'RS512' => new Sha512(),
            default => throw new \InvalidArgumentException(
                "Unsupported JWT algorithm: {$this->algorithm}. Supported: RS256, RS384, RS512.",
            ),
        };
    }

    /**
     * Load the RSA private key for token signing.
     *
     * @return \Lcobucci\JWT\Signer\Key
     */
    private function loadPrivateKey(): \Lcobucci\JWT\Signer\Key
    {
        $path       = (string) config('jwt.private_key_path');
        $passphrase = (string) config('jwt.private_key_passphrase', '');
        $absPath    = $this->resolveKeyPath($path);

        if (file_exists($absPath)) {
            return InMemory::file($absPath, $passphrase);
        }

        // Fallback: read from environment (useful in Kubernetes secrets).
        $envKey = env('JWT_PRIVATE_KEY');
        if ($envKey !== null) {
            return InMemory::plainText($envKey, $passphrase);
        }

        throw new \RuntimeException("JWT private key not found at: {$absPath}");
    }

    /**
     * Load the RSA public key for token verification.
     *
     * @return \Lcobucci\JWT\Signer\Key
     */
    private function loadPublicKey(): \Lcobucci\JWT\Signer\Key
    {
        $path    = (string) config('jwt.public_key_path');
        $absPath = $this->resolveKeyPath($path);

        if (file_exists($absPath)) {
            return InMemory::file($absPath);
        }

        $envKey = env('JWT_PUBLIC_KEY');
        if ($envKey !== null) {
            return InMemory::plainText($envKey);
        }

        throw new \RuntimeException("JWT public key not found at: {$absPath}");
    }

    /**
     * Resolve a possibly relative key path to an absolute path.
     *
     * @param  string  $path
     * @return string
     */
    private function resolveKeyPath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return base_path($path);
    }
}
