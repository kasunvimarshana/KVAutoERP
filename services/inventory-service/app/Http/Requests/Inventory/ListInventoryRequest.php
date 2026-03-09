<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * List Inventory Request.
 */
class ListInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search'              => ['sometimes', 'string', 'max:255'],
            'filters'             => ['sometimes', 'array'],
            'filters.status'      => ['sometimes', 'string', 'in:active,inactive,discontinued'],
            'filters.category_id' => ['sometimes', 'string', 'uuid'],
            'filters.warehouse_id' => ['sometimes', 'string', 'uuid'],
            'sort_by'             => ['sometimes', 'string', 'in:name,sku,quantity,unit_price,created_at'],
            'sort_dir'            => ['sometimes', 'string', 'in:asc,desc'],
            'page'                => ['sometimes', 'integer', 'min:1'],
            'per_page'            => ['sometimes', 'integer', 'min:1', 'max:200'],
            'with'                => ['sometimes', 'array'],
        ];
    }
}
