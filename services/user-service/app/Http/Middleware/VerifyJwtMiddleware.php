<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Predis\Client as RedisClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies inbound Bearer JWT tokens using the Auth service public key.
 *
 * The user service never holds the private key — it only verifies
 * tokens that were issued by the centralised Auth service.
 *
 * Verification pipeline:
 *   1. Extract Bearer token from the Authorization header.
 *   2. Parse and validate the signature (RS256, public key only).
 *   3. Validate expiry with configurable leeway.
 *   4. Check the Redis revocation list: JTI-level and token_version-level.
 *   5. Check device-level revocation.
 *   6. Attach verified claims to `$request->attributes` as `jwt_claims`.
 *   7. Store the raw token string as `raw_token` in request attributes.
 *
 * On any failure a 401 JSON response is returned immediately.
 */
final class VerifyJwtMiddleware
{
    public function __construct(
        private readonly RedisClient $redis,
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Request                        $request
     * @param  Closure(Request): Response     $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $rawToken = $this->extractBearerToken($request);

        if ($rawToken === null) {
            return ApiResponse::unauthorized('No authentication token provided.');
        }

        $jwtConfig = $this->buildJwtConfiguration();

        try {
            $token = $jwtConfig->parser()->parse($rawToken);
        } catch (\Throwable) {
            return ApiResponse::unauthorized('Token could not be parsed.');
        }

        // Validate signature and expiry.
        try {
            $jwtConfig->validator()->assert($token, ...$jwtConfig->validationConstraints());
        } catch (RequiredConstraintsViolated $e) {
            return ApiResponse::unauthorized('Token validation failed: ' . $e->getMessage());
        }

        // Extract all claims into a plain PHP array.
        $claims = [];
        foreach ($token->claims()->all() as $name => $value) {
            $claims[$name] = $value instanceof \DateTimeInterface
                ? $value->getTimestamp()
                : $value;
        }

        // Check Redis revocation list for individual JTI.
        $jti = (string) ($claims['jti'] ?? '');
        if ($jti !== '' && $this->isJtiRevoked($jti)) {
            return ApiResponse::unauthorized('Token has been revoked.');
        }

        // Check token_version against the user's current version stored in Redis.
        $userId       = (string) ($claims['user_id'] ?? '');
        $tokenVersion = isset($claims['token_version']) ? (int) $claims['token_version'] : null;

        if ($userId !== '' && $tokenVersion !== null && $this->isTokenVersionStale($userId, $tokenVersion)) {
            return ApiResponse::unauthorized('Token version is no longer valid. Please log in again.');
        }

        // Check device-level revocation.
        $deviceId = (string) ($claims['device_id'] ?? '');
        if ($userId !== '' && $deviceId !== '' && $this->isDeviceRevoked($userId, $deviceId)) {
            return ApiResponse::unauthorized('This device session has been revoked.');
        }

        $request->attributes->set('jwt_claims', $claims);
        $request->attributes->set('raw_token', $rawToken);

        return $next($request);
    }

    /**
     * Build the lcobucci/jwt Configuration for RS256 public-key verification.
     *
     * @return Configuration
     */
    private function buildJwtConfiguration(): Configuration
    {
        $publicKeyPath = config('user_service.jwt.public_key');

        $publicKeyContent = is_file($publicKeyPath)
            ? file_get_contents($publicKeyPath)
            : $publicKeyPath;

        if ($publicKeyContent === false || $publicKeyContent === '') {
            throw new \RuntimeException('JWT public key could not be loaded from: ' . $publicKeyPath);
        }

        $signer    = new Sha256();
        $publicKey = InMemory::plainText($publicKeyContent);

        $leewaySeconds = (int) config('user_service.jwt.leeway_seconds', 30);
        $clock         = SystemClock::fromSystemTimezone();

        $config = Configuration::forAsymmetricSigner(
            $signer,
            InMemory::plainText(''),  // No private key needed in a downstream service.
            $publicKey,
        );

        $config->setValidationConstraints(
            new SignedWith($signer, $publicKey),
            new LooseValidAt($clock, new \DateInterval("PT{$leewaySeconds}S")),
        );

        return $config;
    }

    /**
     * Extract the raw Bearer token from the Authorization header.
     *
     * @param  Request  $request
     * @return string|null
     */
    private function extractBearerToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (!is_string($header) || !str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = trim(substr($header, 7));

        return $token !== '' ? $token : null;
    }

    /**
     * Check whether a specific JTI has been added to the revocation list.
     *
     * @param  string  $jti
     * @return bool
     */
    private function isJtiRevoked(string $jti): bool
    {
        try {
            $prefix = config('user_service.revocation.jti_prefix', 'revoke:jti:');
            $result = $this->redis->get($prefix . $jti);

            return $result !== null;
        } catch (\Throwable) {
            // Fail open: if Redis is unavailable, don't block valid requests.
            return false;
        }
    }

    /**
     * Check whether the token's version is older than the user's current
     * token_version stored in Redis (set on global logout).
     *
     * @param  string  $userId
     * @param  int     $tokenVersion
     * @return bool
     */
    private function isTokenVersionStale(string $userId, int $tokenVersion): bool
    {
        try {
            $prefix         = config('user_service.revocation.version_prefix', 'revoke:user:');
            $currentVersion = $this->redis->get($prefix . $userId);

            if ($currentVersion === null) {
                return false;
            }

            return $tokenVersion < (int) $currentVersion;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check whether this specific device session has been revoked.
     *
     * @param  string  $userId
     * @param  string  $deviceId
     * @return bool
     */
    private function isDeviceRevoked(string $userId, string $deviceId): bool
    {
        try {
            $prefix = config('user_service.revocation.device_prefix', 'revoke:device:');
            $result = $this->redis->get($prefix . $userId . ':' . $deviceId);

            return $result !== null;
        } catch (\Throwable) {
            return false;
        }
    }
}
