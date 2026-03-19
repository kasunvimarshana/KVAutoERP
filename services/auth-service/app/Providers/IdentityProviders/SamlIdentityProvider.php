<?php

declare(strict_types=1);

namespace App\Providers\IdentityProviders;

use App\Contracts\IdentityProviderContract;
use App\DTOs\AuthResultDto;
use App\DTOs\TokenPairDto;
use App\DTOs\UserInfoDto;
use App\Exceptions\AuthenticationException;
use DOMDocument;
use DOMXPath;

/**
 * SAML 2.0 identity provider adapter.
 *
 * Supports the SAML HTTP-POST binding.  In the Auth service flow:
 *  1. SsoController::redirect() — generates an AuthnRequest and redirects to the IdP.
 *  2. The IdP authenticates the user and POSTs a base64-encoded SAMLResponse
 *     to the Assertion Consumer Service (ACS) URL.
 *  3. SsoController::callback() (or AuthService::login()) calls
 *     exchangeToken($samlResponse, $acsUrl) which parses the assertion,
 *     validates it, and returns an internal TokenPairDto.
 *  4. getUserInfo($subjectId) extracts the identity from the parsed assertion.
 *
 * For production deployments it is recommended to replace the built-in XML
 * parsing with a dedicated SAML library (e.g. OneLogin php-saml) which provides
 * full signature validation and schema verification.
 */
class SamlIdentityProvider implements IdentityProviderContract
{
    private readonly string $entityId;
    private readonly string $ssoUrl;
    private readonly string $sloUrl;
    private readonly string $idpCertificate;
    private readonly string $spPrivateKey;
    private readonly string $spCertificate;
    private readonly string $nameIdFormat;

    /** @var array<string, mixed> Parsed assertion attributes from the last SAMLResponse */
    private array $assertionAttributes = [];

    public function __construct(private readonly array $config = [])
    {
        $this->entityId      = $config['entity_id']      ?? '';
        $this->ssoUrl        = $config['sso_url']        ?? '';
        $this->sloUrl        = $config['slo_url']        ?? '';
        $this->idpCertificate = $config['idp_certificate'] ?? '';
        $this->spPrivateKey   = $config['sp_private_key']  ?? '';
        $this->spCertificate  = $config['sp_certificate']  ?? '';
        $this->nameIdFormat   = $config['name_id_format']
            ?? 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress';
    }

    /**
     * Authenticate via SAML HTTP-POST binding.
     *
     * For the resource-owner flow the $credentials array is expected to contain:
     *   - saml_response : base64-encoded SAMLResponse XML from the IdP.
     */
    public function authenticate(array $credentials): AuthResultDto
    {
        $samlResponse = $credentials['saml_response'] ?? '';

        if (empty($samlResponse)) {
            throw new AuthenticationException('SAML response is required for SAML authentication');
        }

        $userInfo = $this->parseSamlResponse($samlResponse);

        return new AuthResultDto(
            accessToken:  '',   // Will be issued by AuthService after user lookup
            refreshToken: '',
            expiresIn:    3600,
            claims:       [
                'external_id' => $userInfo->externalId,
                'email'       => $userInfo->email,
                'name'        => $userInfo->name,
                'first_name'  => $userInfo->firstName,
                'last_name'   => $userInfo->lastName,
                'provider'    => $userInfo->provider,
            ],
        );
    }

    /**
     * Process the SAML authorization code (base64-encoded SAMLResponse).
     *
     * In SAML parlance the "code" passed from SsoController::callback() is the
     * raw SAMLResponse value POSTed by the IdP to the ACS URL.
     */
    public function exchangeToken(string $code, string $redirectUri): TokenPairDto
    {
        // Parse the SAMLResponse to obtain user identity
        $userInfo = $this->parseSamlResponse($code);

        // SAML does not issue OAuth2-style tokens; return the NameID as a
        // pseudo-token so upstream code can call getUserInfo().
        return new TokenPairDto(
            accessToken:  $userInfo->externalId,
            refreshToken: '',
            expiresIn:    3600,
        );
    }

    /**
     * Return the user info extracted from the most-recently parsed assertion.
     *
     * The $accessToken parameter is the NameID returned by exchangeToken().
     */
    public function getUserInfo(string $accessToken): UserInfoDto
    {
        $attrs = $this->assertionAttributes;

        return new UserInfoDto(
            externalId: $accessToken,
            email:      $attrs['email']      ?? $attrs['Email']      ?? $attrs['emailAddress'] ?? $accessToken,
            name:       $attrs['displayName'] ?? $attrs['cn']        ?? ($attrs['givenName'] ?? '') . ' ' . ($attrs['sn'] ?? ''),
            firstName:  $attrs['givenName']   ?? $attrs['firstName'] ?? null,
            lastName:   $attrs['sn']          ?? $attrs['lastName']  ?? null,
            provider:   'saml',
        );
    }

    /**
     * Initiate SAML Single Logout.
     * Redirects to the IdP's SLO endpoint (not applicable in a REST-only context;
     * callers should redirect the user agent to the returned URL).
     */
    public function logout(string $accessToken): void
    {
        // SLO is handled at the HTTP layer; in a REST API this is a no-op.
        // Implementations integrating a browser flow should redirect to $this->sloUrl.
    }

    /**
     * SAML does not issue refresh tokens; always throws.
     */
    public function refreshToken(string $refreshToken): TokenPairDto
    {
        throw new AuthenticationException('SAML provider does not support token refresh. Re-authenticate via SSO.');
    }

    public function getProviderName(): string
    {
        return 'saml';
    }

    public function supportsSSO(): bool
    {
        return true;
    }

    // ──────────────────────────────────────────────────────────
    // SAML AuthnRequest generation
    // ──────────────────────────────────────────────────────────

    /**
     * Build a SAML 2.0 AuthnRequest and return the IdP redirect URL.
     *
     * @return string URL to redirect the user agent to
     */
    public function buildAuthnRequestUrl(string $acsUrl, string $relayState = ''): string
    {
        if (empty($this->ssoUrl)) {
            throw new AuthenticationException('SAML SSO URL is not configured');
        }

        $id        = '_' . bin2hex(random_bytes(20));
        $issueInstant = gmdate('Y-m-d\TH:i:s\Z');

        $xml = <<<XML
<samlp:AuthnRequest
    xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
    ID="{$id}"
    Version="2.0"
    IssueInstant="{$issueInstant}"
    Destination="{$this->ssoUrl}"
    AssertionConsumerServiceURL="{$acsUrl}"
    ProtocolBinding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST">
    <saml:Issuer>{$this->entityId}</saml:Issuer>
    <samlp:NameIDPolicy
        Format="{$this->nameIdFormat}"
        AllowCreate="true"/>
</samlp:AuthnRequest>
XML;

        $encoded = base64_encode(gzdeflate($xml));

        $params = ['SAMLRequest' => $encoded];

        if ($relayState) {
            $params['RelayState'] = $relayState;
        }

        return $this->ssoUrl . '?' . http_build_query($params);
    }

    // ──────────────────────────────────────────────────────────
    // SAMLResponse parsing
    // ──────────────────────────────────────────────────────────

    /**
     * Decode and parse a base64-encoded SAMLResponse, extract the NameID and
     * attribute statements, and optionally verify the IdP signature.
     *
     * @throws AuthenticationException on malformed or invalid response
     */
    private function parseSamlResponse(string $samlResponseBase64): UserInfoDto
    {
        $xml = base64_decode($samlResponseBase64, strict: true);

        if ($xml === false) {
            throw new AuthenticationException('Invalid SAML response encoding');
        }

        // Suppress libxml errors for controlled handling
        $prevLibxmlErrors = libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $loaded = $doc->loadXML($xml);

        libxml_use_internal_errors($prevLibxmlErrors);

        if (! $loaded) {
            throw new AuthenticationException('Failed to parse SAML response XML');
        }

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('samlp',  'urn:oasis:names:tc:SAML:2.0:protocol');
        $xpath->registerNamespace('saml',   'urn:oasis:names:tc:SAML:2.0:assertion');
        $xpath->registerNamespace('ds',     'http://www.w3.org/2000/09/xmldsig#');

        // Validate top-level status
        $status = $xpath->evaluate('string(//samlp:StatusCode/@Value)');

        if ($status !== 'urn:oasis:names:tc:SAML:2.0:status:Success' && ! empty($status)) {
            throw new AuthenticationException("SAML authentication failed with status: {$status}");
        }

        // Optionally validate IdP signature
        if (! empty($this->idpCertificate)) {
            $this->verifySignature($doc, $xpath);
        }

        // Extract NameID
        $nameId = $xpath->evaluate('string(//saml:NameID)');

        if (empty($nameId)) {
            throw new AuthenticationException('SAML response does not contain a NameID');
        }

        // Extract attribute statements
        $attributes = [];
        $attrNodes  = $xpath->query('//saml:Attribute');

        if ($attrNodes !== false) {
            foreach ($attrNodes as $attrNode) {
                $attrName   = (string) $attrNode->getAttribute('Name');
                $valueNodes = $xpath->query('saml:AttributeValue', $attrNode);

                $values = [];
                if ($valueNodes !== false) {
                    foreach ($valueNodes as $valueNode) {
                        $values[] = $valueNode->textContent;
                    }
                }

                $attributes[$attrName] = count($values) === 1 ? $values[0] : $values;
            }
        }

        $this->assertionAttributes = $attributes;

        return new UserInfoDto(
            externalId: $nameId,
            email:      $attributes['email'] ?? $attributes['Email'] ?? $attributes['emailAddress'] ?? $nameId,
            name:       $attributes['displayName'] ?? $attributes['cn'] ?? '',
            firstName:  $attributes['givenName'] ?? $attributes['firstName'] ?? null,
            lastName:   $attributes['sn']         ?? $attributes['lastName']  ?? null,
            provider:   'saml',
        );
    }

    /**
     * Verify the XML-DSIG signature on the SAML Response using the configured
     * IdP certificate.
     *
     * @throws AuthenticationException when the signature is missing or invalid
     */
    private function verifySignature(DOMDocument $doc, DOMXPath $xpath): void
    {
        $signatureNodes = $xpath->query('//ds:Signature');

        if ($signatureNodes === false || $signatureNodes->length === 0) {
            throw new AuthenticationException('SAML response is unsigned — signature verification is required');
        }

        // Extract the signed XML and verify with the IdP public certificate
        $signatureValue = $xpath->evaluate('string(//ds:SignatureValue)');
        $signedInfo     = $xpath->evaluate('string(//ds:SignedInfo)');

        if (empty($signatureValue) || empty($signedInfo)) {
            throw new AuthenticationException('Malformed SAML signature');
        }

        // Normalise the certificate
        $cert    = $this->normaliseCertificate($this->idpCertificate);
        $pubKey  = openssl_pkey_get_public($cert);

        if (! $pubKey) {
            throw new AuthenticationException('Invalid IdP certificate');
        }

        // Rebuild the canonical SignedInfo for verification
        // (In production, use the proper c14n transform from the XML-DSIG spec)
        $signedInfoXml = '';

        foreach ($xpath->query('//ds:SignedInfo') as $node) {
            $signedInfoXml = $doc->saveXML($node);
        }

        $decoded = base64_decode($signatureValue, strict: true);

        if ($decoded === false) {
            throw new AuthenticationException('Invalid SAML signature encoding');
        }

        $valid = openssl_verify($signedInfoXml, $decoded, $pubKey, OPENSSL_ALGO_SHA256);

        if ($valid !== 1) {
            throw new AuthenticationException('SAML signature verification failed');
        }
    }

    /**
     * Wrap a raw base64 certificate string into PEM format if not already wrapped.
     */
    private function normaliseCertificate(string $cert): string
    {
        $cert = trim($cert);

        if (str_starts_with($cert, '-----BEGIN CERTIFICATE-----')) {
            return $cert;
        }

        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split($cert, 64, "\n")
            . "-----END CERTIFICATE-----\n";
    }
}
