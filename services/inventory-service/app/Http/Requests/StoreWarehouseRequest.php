<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for creating a warehouse.
 */
final class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code'          => 'required|string|max:50',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'type'          => 'nullable|string|in:' . implode(',', Warehouse::TYPES),
            'status'        => 'nullable|string|in:' . implode(',', Warehouse::STATUSES),
            'address_line1' => 'nullable|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|size:3',
            'postal_code'   => 'nullable|string|max:20',
            'is_default'    => 'nullable|boolean',
            'metadata'      => 'nullable|array',
            'organization_id' => 'nullable|uuid',
            'branch_id'     => 'nullable|uuid',
        ];
    }
}
