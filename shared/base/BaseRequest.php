<?php

declare(strict_types=1);

namespace App\Shared\Base;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Abstract Base Form Request.
 *
 * All form requests across KV_SAAS micro-services should extend this class
 * to inherit:
 *  - Consistent JSON 422 responses on validation failure
 *  - Tenant and user helper methods
 *  - Default open authorisation (override in concrete classes)
 */
abstract class BaseRequest extends FormRequest
{
    // ─────────────────────────────────────────────────────────────────────────
    // Authorisation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Default to authorised.
     *
     * Concrete request classes may override this to perform granular checks.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Validation failure
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Override to always return a JSON 422 response instead of a redirect,
     * regardless of whether the request was made via AJAX.
     *
     * @param  Validator  $validator
     * @return never
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(
            response()->json(
                data: [
                    'success' => false,
                    'message' => 'Validation failed',
                    'data'    => null,
                    'meta'    => ['request_id' => $this->header('X-Request-ID')],
                    'errors'  => $validator->errors()->toArray(),
                ],
                status: 422,
            )
        );
    }

    /**
     * Override to return a JSON 403 response when authorization fails.
     *
     * @return never
     *
     * @throws HttpResponseException
     */
    protected function failedAuthorization(): never
    {
        throw new HttpResponseException(
            response()->json(
                data: [
                    'success' => false,
                    'message' => 'This action is unauthorized.',
                    'data'    => null,
                    'meta'    => ['request_id' => $this->header('X-Request-ID')],
                    'errors'  => [],
                ],
                status: 403,
            )
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Multi-tenant helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return the TenantInterface instance for the current request context.
     *
     * @return \App\Shared\Contracts\TenantInterface|null
     */
    public function tenant(): ?\App\Shared\Contracts\TenantInterface
    {
        try {
            return app(\App\Shared\Contracts\TenantInterface::class);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Return the current tenant ID.
     *
     * Reads (in priority order):
     *  1. The resolved TenantInterface from the service container.
     *  2. The X-Tenant-ID header.
     *  3. null.
     *
     * @return string|null
     */
    public function tenantId(): ?string
    {
        $tenant = $this->tenant();

        if ($tenant !== null) {
            try {
                return $tenant->getTenantId();
            } catch (\Throwable) {
                // Fall through
            }
        }

        return $this->header('X-Tenant-ID') ?: null;
    }

    /**
     * Return the currently authenticated user's ID.
     *
     * @return string|int|null
     */
    public function userId(): string|int|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Convenience helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Merge tenant_id into the validated data bag automatically.
     *
     * Call from prepareForValidation() in concrete requests that need it:
     *   protected function prepareForValidation(): void { $this->mergeTenantId(); }
     *
     * @return void
     */
    protected function mergeTenantId(): void
    {
        if ($tenantId = $this->tenantId()) {
            $this->merge(['tenant_id' => $tenantId]);
        }
    }

    /**
     * Merge the authenticated user's ID into the request data.
     *
     * @return void
     */
    protected function mergeUserId(): void
    {
        if ($userId = $this->userId()) {
            $this->merge(['user_id' => $userId]);
        }
    }
}
