<?php

declare(strict_types=1);

namespace App\Shared\Base;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Abstract Base API Resource.
 *
 * Provides a consistent outer envelope for all JSON API resources across
 * KV_SAAS micro-services, including standard fields (id, created_at,
 * updated_at), optional tenant scoping, and extensible meta support.
 *
 * Usage:
 *   class ProductResource extends BaseResource
 *   {
 *       protected function resourceData(Request $request): array
 *       {
 *           return ['name' => $this->name, 'sku' => $this->sku];
 *       }
 *   }
 */
abstract class BaseResource extends JsonResource
{
    /** Whether to include the tenant_id field in the output. */
    private bool $includeTenant = false;

    /** Additional meta fields to merge into the response envelope. */
    private array $additionalMeta = [];

    // ─────────────────────────────────────────────────────────────────────────
    // Abstract API – subclasses provide domain-specific fields
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return the domain-specific fields for this resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    abstract protected function resourceData(\Illuminate\Http\Request $request): array;

    // ─────────────────────────────────────────────────────────────────────────
    // JsonResource override
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * {@inheritDoc}
     *
     * Merges standard fields with domain fields from {@see resourceData()}.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $base = [
            'id'         => $this->resource->id ?? $this->resource['id'] ?? null,
            'created_at' => $this->formatTimestamp(
                $this->resource->created_at ?? $this->resource['created_at'] ?? null
            ),
            'updated_at' => $this->formatTimestamp(
                $this->resource->updated_at ?? $this->resource['updated_at'] ?? null
            ),
        ];

        if ($this->includeTenant) {
            $base['tenant_id'] = $this->resource->tenant_id
                ?? $this->resource['tenant_id']
                ?? null;
        }

        $domainData = $this->resourceData($request);

        return array_merge($base, $domainData);
    }

    /**
     * {@inheritDoc}
     *
     * Appends additional meta data to the resource's wrapping envelope.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function with(\Illuminate\Http\Request $request): array
    {
        return array_merge(
            ['meta' => $this->additionalMeta],
            parent::with($request),
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fluent configuration
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Include the tenant_id field in the serialised output.
     *
     * @return static
     */
    public function withTenantId(): static
    {
        $this->includeTenant = true;

        return $this;
    }

    /**
     * Merge additional metadata into the `meta` envelope key.
     *
     * @param  array<string, mixed>  $meta
     * @return static
     */
    public function withMeta(array $meta): static
    {
        $this->additionalMeta = array_merge($this->additionalMeta, $meta);

        return $this;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Format a timestamp into ISO-8601 or return null.
     *
     * @param  mixed  $value
     * @return string|null
     */
    private function formatTimestamp(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Helper to safely access a nested resource attribute, falling back to a
     * default if not set.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    protected function attr(string $key, mixed $default = null): mixed
    {
        if (is_object($this->resource)) {
            return $this->resource->{$key} ?? $default;
        }

        if (is_array($this->resource)) {
            return $this->resource[$key] ?? $default;
        }

        return $default;
    }
}
