<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Providers\IdentityProviders\SamlIdentityProvider;
use App\Services\IdentityProviderManager;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SsoController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly IdentityProviderManager $providerManager,
    ) {}

    /**
     * GET /api/v1/sso/redirect
     *
     * Redirect to the IAM provider's authorization endpoint.
     * For SAML providers, generates a SAML AuthnRequest.
     * For OAuth2/OIDC providers, builds the standard authorization URL.
     */
    public function redirect(Request $request): RedirectResponse|JsonResponse
    {
        $provider    = $request->query('provider', 'okta');
        $tenantId    = $request->query('tenant_id', '');
        $state       = Str::random(40);
        $redirectUri = $request->query('redirect_uri', config('app.url') . '/api/v1/sso/callback');

        $request->session()->put('sso_state', $state);
        $request->session()->put('sso_provider', $provider);
        $request->session()->put('sso_tenant_id', $tenantId);

        // SAML flow: delegate AuthnRequest generation to the adapter
        if ($provider === 'saml') {
            /** @var SamlIdentityProvider $idp */
            $idp     = $this->providerManager->resolve('saml', (string) $tenantId);
            $authUrl = $idp->buildAuthnRequestUrl($redirectUri, $state);

            return redirect()->away($authUrl);
        }

        // OAuth2 / OIDC flow
        $config  = config("iam_providers.{$provider}", []);
        $authUrl = ($config['authorization_endpoint'] ?? '') . '?' . http_build_query([
            'response_type' => 'code',
            'client_id'     => $config['client_id'] ?? '',
            'redirect_uri'  => $redirectUri,
            'scope'         => $config['scope'] ?? 'openid profile email',
            'state'         => $state,
        ]);

        if (empty($config['authorization_endpoint'])) {
            return $this->errorResponse(
                message: "Provider '{$provider}' has no authorization endpoint configured",
                statusCode: 400,
            );
        }

        return redirect()->away($authUrl);
    }

    /**
     * GET /api/v1/sso/callback
     *
     * Handle the OAuth2 authorization code callback.
     */
    public function callback(Request $request): JsonResponse
    {
        $state    = $request->query('state', '');
        $code     = $request->query('code', '');
        $provider = $request->session()->get('sso_provider', 'okta');
        $tenantId = $request->session()->get('sso_tenant_id', '');

        if ($state !== $request->session()->get('sso_state')) {
            return $this->errorResponse('Invalid SSO state — possible CSRF attack', statusCode: 400);
        }

        if (empty($code)) {
            return $this->errorResponse('Authorization code missing', statusCode: 400);
        }

        $idp       = $this->providerManager->resolve($provider, $tenantId);
        $redirectUri = config('app.url') . '/api/v1/sso/callback';
        $tokenPair = $idp->exchangeToken($code, $redirectUri);

        return $this->successResponse(
            data:    $tokenPair->toArray(),
            message: 'SSO authentication successful',
        );
    }

    /**
     * GET /api/v1/sso/providers
     *
     * List registered SSO providers.
     */
    public function providers(): JsonResponse
    {
        return $this->successResponse(
            data:    $this->providerManager->getRegisteredProviders(),
            message: 'Registered IAM providers retrieved',
        );
    }
}
