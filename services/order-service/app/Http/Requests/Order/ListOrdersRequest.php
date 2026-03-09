<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

/**
 * List Orders Request.
 */
class ListOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'search'                  => ['sometimes', 'string', 'max:255'],
            'filters'                 => ['sometimes', 'array'],
            'filters.status'          => ['sometimes', 'string', 'in:pending,confirmed,processing,fulfilled,cancelled,failed'],
            'filters.customer_id'     => ['sometimes', 'string'],
            'sort_by'                 => ['sometimes', 'string', 'in:created_at,total_amount,status'],
            'sort_dir'                => ['sometimes', 'string', 'in:asc,desc'],
            'page'                    => ['sometimes', 'integer', 'min:1'],
            'per_page'                => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
