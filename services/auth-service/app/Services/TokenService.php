<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenServiceContract;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use RuntimeException;

class TokenService implements TokenServiceContract
{
    private readonly string $privateKey;
    private readonly string $publicKey;
    private readonly string $issuer;

    public function __construct()
    {
        $privateKeyPath = config('jwt.private_key_path');
        $publicKeyPath  = config('jwt.public_key_path');

        if (! file_exists($privateKeyPath) || ! file_exists($publicKeyPath)) {
            // In test/CI environments the keys may not exist yet
            $this->privateKey = '';
            $this->publicKey  = '';
        } else {
            $this->privateKey = (string) file_get_contents($privateKeyPath);
            $this->publicKey  = (string) file_get_contents($publicKeyPath);
        }

        $this->issuer = (string) config('jwt.issuer', 'kv-saas-auth');
    }

    public function issue(array $claims, int $ttl): string
    {
        if (empty($this->privateKey)) {
            throw new RuntimeException('JWT private key not configured. Run: php artisan jwt:generate-keys');
        }

        $now     = time();
        $payload = array_merge($claims, [
            'jti' => $claims['jti'] ?? (string) Str::uuid(),
            'iss' => $this->issuer,
            'iat' => $now,
            'exp' => $now + $ttl,
            'nbf' => $now,
        ]);

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    public function issueRefreshToken(string $userId, string $deviceId, string $jti): string
    {
        $refreshToken = Str::random(64);
        $ttl          = (int) config('jwt.refresh_ttl', 2592000);

        Redis::setex("refresh:{$refreshToken}", $ttl, json_encode([
            'user_id'    => $userId,
            'device_id'  => $deviceId,
            'jti'        => $jti,
            'created_at' => time(),
        ]));

        // Track active devices for session management
        $deviceKey = "devices:{$userId}";
        $devices   = json_decode((string) (Redis::get($deviceKey) ?? '[]'), true);

        $devices[$deviceId] = [
            'jti'           => $jti,
            'refresh_token' => $refreshToken,
            'last_active'   => time(),
        ];

        Redis::setex($deviceKey, $ttl, json_encode($devices));

        return $refreshToken;
    }

    public function verify(string $token): array
    {
        if (empty($this->publicKey)) {
            throw new RuntimeException('JWT public key not configured');
        }

        try {
            $decoded = JWT::decode($token, new Key($this->publicKey, 'RS256'));
            $claims  = (array) $decoded;

            if ($this->isRevoked($claims['jti'] ?? '')) {
                throw new RuntimeException('Token has been revoked');
            }

            return $claims;
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new RuntimeException('Token verification failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function decode(string $token, bool $verify = true): array
    {
        if ($verify) {
            return $this->verify($token);
        }

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid token format');
        }

        $payload = base64_decode(strtr($parts[1], '-_', '+/'));

        return (array) json_decode($payload, true);
    }

    public function revoke(string $jti): void
    {
        if (empty($jti)) {
            return;
        }

        $ttl = (int) config('jwt.ttl', 900);
        Redis::setex("revoked:{$jti}", $ttl + 300, '1');
    }

    public function isRevoked(string $jti): bool
    {
        if (empty($jti)) {
            return false;
        }

        return (bool) Redis::exists("revoked:{$jti}");
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Return the public key as a JSON Web Key Set (JWKS).
     *
     * Downstream microservices can call GET /api/v1/auth/.well-known/jwks.json
     * to obtain the key and verify tokens locally using standard JWKS libraries.
     */
    public function getJwks(): array
    {
        if (empty($this->publicKey)) {
            return ['keys' => []];
        }

        $pubKey  = openssl_pkey_get_public($this->publicKey);

        if (! $pubKey) {
            return ['keys' => []];
        }

        $details = openssl_pkey_get_details($pubKey);

        if (! $details || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            return ['keys' => []];
        }

        $rsa = $details['rsa'];

        return [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'kid' => hash('sha256', $this->publicKey),
                    'n'   => rtrim(strtr(base64_encode($rsa['n']), '+/', '-_'), '='),
                    'e'   => rtrim(strtr(base64_encode($rsa['e']), '+/', '-_'), '='),
                ],
            ],
        ];
    }

    public function buildClaims(array $user, string $deviceId, string $tenantId): array
    {
        return [
            'jti'           => (string) Str::uuid(),
            'sub'           => $user['id'],
            'tenant_id'     => $tenantId ?: ($user['tenant_id'] ?? ''),
            'org_id'        => $user['organization_id'] ?? '',
            'branch_id'     => $user['branch_id'] ?? '',
            'roles'         => $user['roles'] ?? [],
            'permissions'   => $user['permissions'] ?? [],
            'device_id'     => $deviceId,
            'token_version' => $user['token_version'] ?? 1,
            'provider'      => $user['iam_provider'] ?? 'local',
            'type'          => 'access',
        ];
    }
}
