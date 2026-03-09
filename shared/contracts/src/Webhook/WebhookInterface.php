<?php

declare(strict_types=1);

namespace Saas\Contracts\Webhook;

/**
 * Contract for outbound webhook delivery and verification.
 *
 * Implementations are responsible for HTTP delivery, HMAC signing, and
 * retry scheduling.  This interface is intentionally transport-agnostic so
 * that the underlying HTTP client can be swapped without touching consumers.
 */
interface WebhookInterface
{
    /**
     * Sends a webhook payload to the given URL.
     *
     * The implementation SHOULD:
     * - Set a reasonable connect/read timeout.
     * - Add standard platform headers (`X-Webhook-Id`, `X-Webhook-Timestamp`).
     * - Include an HMAC signature header when `$secret` is provided.
     * - Return `false` (and log) rather than throwing on non-2xx responses.
     *
     * @param string               $url     Destination endpoint URL.
     * @param array<string, mixed> $payload Event payload to serialise as JSON.
     * @param array<string, string> $headers Additional HTTP headers to include.
     * @param string               $secret  Signing secret; omit or pass empty string to skip signing.
     *
     * @return bool `true` when the remote endpoint responded with a 2xx status.
     */
    public function send(string $url, array $payload, array $headers = [], string $secret = ''): bool;

    /**
     * Verifies an inbound webhook signature.
     *
     * Implementations MUST use a constant-time comparison to prevent timing
     * attacks.  The expected HMAC is computed over `$payload` using `$secret`.
     *
     * @param string $payload   Raw request body received from the webhook sender.
     * @param string $signature Signature value extracted from the request header.
     * @param string $secret    The shared secret used to compute the expected HMAC.
     *
     * @return bool `true` when the computed signature matches `$signature`.
     */
    public function verify(string $payload, string $signature, string $secret): bool;

    /**
     * Schedules or immediately retries a previously failed webhook delivery.
     *
     * @param string $webhookId Identifier of the failed webhook delivery record.
     *
     * @return bool `true` when the retry was accepted (not necessarily delivered).
     */
    public function retry(string $webhookId): bool;
}
