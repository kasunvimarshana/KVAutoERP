<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Application\Auth\DTOs\UserDTO;
use App\Domain\Tenant\Entities\Tenant;
use App\Domain\User\Entities\User;
use App\Http\Resources\UserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use Throwable;

/**
 * SSO controller for OAuth2 social logins and internal cross-service token validation.
 */
class SSOController extends Controller
{
    use ApiResponse;

    /** Supported SSO providers and their Socialite driver names */
    private const SUPPORTED_PROVIDERS = ['google', 'microsoft', 'github', 'okta', 'saml'];

    /**
     * Redirect the user to the OAuth provider's authorization page.
     */
    public function redirect(string $provider): JsonResponse
    {
        $this->ensureProviderSupported($provider);

        try {
            /** @var \Laravel\Socialite\Contracts\Factory $socialite */
            $socialite = app(\Laravel\Socialite\Contracts\Factory::class);

            // Return the authorization URL for the SPA to redirect to.
            $url = $socialite->driver($provider)->stateless()->redirect()->getTargetUrl();

            return $this->success(['redirect_url' => $url], "Redirect to {$provider} authorization.");
        } catch (Throwable $e) {
            Log::error('SSO redirect failed', ['provider' => $provider, 'error' => $e->getMessage()]);

            return $this->serverError("SSO redirect for provider [{$provider}] failed.");
        }
    }

    /**
     * Handle the OAuth provider callback and issue a Passport token.
     */
    public function callback(string $provider, Request $request): JsonResponse
    {
        $this->ensureProviderSupported($provider);

        try {
            /** @var \Laravel\Socialite\Contracts\Factory $socialite */
            $socialite    = app(\Laravel\Socialite\Contracts\Factory::class);
            $socialUser   = $socialite->driver($provider)->stateless()->user();
            $tenant       = app(Tenant::class);

            // Find or create user
            /** @var User|null $user */
            $user = User::query()
                ->where('email', $socialUser->getEmail())
                ->where('tenant_id', $tenant->id)
                ->first();

            if ($user === null) {
                /** @var User $user */
                $user = User::create([
                    'tenant_id'         => $tenant->id,
                    'name'              => $socialUser->getName(),
                    'email'             => $socialUser->getEmail(),
                    'password'          => bcrypt(\Illuminate\Support\Str::random(32)),
                    'status'            => User::STATUS_ACTIVE,
                    'email_verified_at' => now(),
                    'metadata'          => [
                        'sso_provider'  => $provider,
                        'sso_id'        => $socialUser->getId(),
                    ],
                ]);

                $user->assignRole('user');
            }

            if (! $user->isActive()) {
                return $this->forbidden('Your account is not active.');
            }

            $tokenResult = $user->createToken(
                "sso:{$provider}:tenant:{$tenant->id}",
                ['read', 'write'],
                now()->addMinutes(config('passport.token_lifetime', 1440)),
            );

            $user->recordLogin();

            return $this->success([
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_in'   => config('passport.token_lifetime', 1440) * 60,
                'user'         => new UserResource(UserDTO::fromEntity($user)),
            ], 'SSO login successful.');
        } catch (Throwable $e) {
            Log::error('SSO callback failed', ['provider' => $provider, 'error' => $e->getMessage()]);

            return $this->serverError("SSO authentication failed.");
        }
    }

    /**
     * Validate a Passport bearer token for cross-service requests.
     * Used by other microservices to verify tokens issued by this auth service.
     */
    public function validateToken(Request $request): JsonResponse
    {
        $bearerToken = $request->bearerToken();

        if (empty($bearerToken)) {
            return $this->unauthorized('No token provided.');
        }

        try {
            // Look up the token hash in Passport's table
            $tokenId    = explode('|', $bearerToken, 2);
            $accessToken = Token::query()
                ->where('id', $tokenId[0] ?? '')
                ->where('revoked', false)
                ->first();

            if ($accessToken === null) {
                // Try standard Passport PAT validation via the guard
                $user = auth('api')->user();

                if ($user === null) {
                    return $this->unauthorized('Token is invalid or has been revoked.');
                }

                return $this->success([
                    'valid'       => true,
                    'user'        => new UserResource(UserDTO::fromEntity($user)),
                    'scopes'      => $user->token()?->scopes ?? [],
                    'tenant_id'   => $user->tenant_id,
                    'expires_at'  => $user->token()?->expires_at?->toIso8601String(),
                ], 'Token is valid.');
            }

            if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return $this->unauthorized('Token has expired.');
            }

            /** @var User|null $user */
            $user = User::find($accessToken->user_id);

            if ($user === null || ! $user->isActive()) {
                return $this->unauthorized('Token user is inactive or not found.');
            }

            return $this->success([
                'valid'      => true,
                'user'       => new UserResource(UserDTO::fromEntity($user)),
                'scopes'     => $accessToken->scopes,
                'tenant_id'  => $user->tenant_id,
                'expires_at' => $accessToken->expires_at?->toIso8601String(),
            ], 'Token is valid.');
        } catch (Throwable $e) {
            Log::error('Token validation failed', ['error' => $e->getMessage()]);

            return $this->serverError('Token validation failed.');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function ensureProviderSupported(string $provider): void
    {
        if (! in_array($provider, self::SUPPORTED_PROVIDERS, true)) {
            abort(400, "Unsupported SSO provider [{$provider}].");
        }
    }
}
