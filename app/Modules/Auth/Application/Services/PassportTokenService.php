<?php

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Contracts\OAuthenticatable;
use Modules\Auth\Application\Contracts\TokenServiceInterface;
use Modules\Auth\Domain\Entities\AccessToken;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Passport-backed token service.
 * Uses Laravel Passport's Personal Access Tokens for issuance/revocation.
 * Swappable via TokenServiceInterface binding in the service provider.
 */
class PassportTokenService implements TokenServiceInterface
{
    private static int $tokenTtlMinutes = 60;

    public function issueToken(int $userId, string $tokenName = 'api', array $scopes = []): AccessToken
    {
        /** @var OAuthenticatable|UserModel $user */
        $user = UserModel::findOrFail($userId);

        $result = $user->createToken($tokenName, $scopes);

        return new AccessToken(
            accessToken: $result->accessToken,
            tokenType: 'Bearer',
            expiresIn: self::$tokenTtlMinutes * 60,
            scopes: $scopes,
        );
    }

    public function revokeCurrentToken(int $userId): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        $token = $user->currentAccessToken();
        if (! $token) {
            return false;
        }

        $token->revoke();

        return true;
    }

    public function revokeAllTokens(int $userId): bool
    {
        /** @var UserModel $user */
        $user = UserModel::find($userId);
        if (! $user) {
            return false;
        }

        $user->tokens()->each(fn ($token) => $token->revoke());

        return true;
    }
}
