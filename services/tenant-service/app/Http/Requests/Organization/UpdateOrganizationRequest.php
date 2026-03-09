<?php

declare(strict_types=1);

namespace App\Http\Requests\Organization;

use App\Domain\Organization\Entities\Organization;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'parent_id'   => ['sometimes', 'nullable', 'uuid'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'status'      => ['sometimes', 'string', 'in:' . implode(',', [
                Organization::STATUS_ACTIVE,
                Organization::STATUS_INACTIVE,
            ])],
            'settings'    => ['sometimes', 'array'],
            'metadata'    => ['sometimes', 'array'],
        ];
    }
}
