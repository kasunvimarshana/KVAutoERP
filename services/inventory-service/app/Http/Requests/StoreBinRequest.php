<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\WarehouseBin;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for creating a warehouse bin.
 */
final class StoreBinRequest extends FormRequest
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
            'code'     => 'required|string|max:50',
            'name'     => 'nullable|string|max:255',
            'zone'     => 'nullable|string|max:50',
            'aisle'    => 'nullable|string|max:50',
            'rack'     => 'nullable|string|max:50',
            'shelf'    => 'nullable|string|max:50',
            'position' => 'nullable|string|max:50',
            'type'     => 'nullable|string|in:' . implode(',', WarehouseBin::TYPES),
            'status'   => 'nullable|string|in:' . implode(',', WarehouseBin::STATUSES),
            'capacity' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ];
    }
}
