<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

/**
 * List Tenants Request.
 *
 * Validates query parameters for filtering, sorting, and pagination.
 */
class ListTenantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search'   => ['sometimes', 'string', 'max:255'],
            'filters'  => ['sometimes', 'array'],
            'filters.status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'filters.plan'   => ['sometimes', 'string'],
            'sort_by'  => ['sometimes', 'string', 'in:name,slug,status,plan,created_at'],
            'sort_dir' => ['sometimes', 'string', 'in:asc,desc'],
            'page'     => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'with'     => ['sometimes', 'array'],
        ];
    }
}
