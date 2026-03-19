<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:200'],
            'entity_type' => ['sometimes', 'string', 'max:200'],
            'states'      => ['sometimes', 'array', 'min:1'],
            'states.*.name'    => ['required_with:states', 'string', 'max:100'],
            'states.*.label'   => ['nullable', 'string', 'max:200'],
            'states.*.initial' => ['nullable', 'boolean'],
            'states.*.final'   => ['nullable', 'boolean'],
            'transitions'      => ['sometimes', 'array', 'min:1'],
            'transitions.*.from'  => ['required_with:transitions', 'string', 'max:100'],
            'transitions.*.to'    => ['required_with:transitions', 'string', 'max:100'],
            'transitions.*.event' => ['required_with:transitions', 'string', 'max:100'],
            'guards'    => ['nullable', 'array'],
            'actions'   => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'metadata'  => ['nullable', 'array'],
        ];
    }
}
