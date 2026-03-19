<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWorkflowDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'   => ['required', 'uuid'],
            'name'        => ['required', 'string', 'max:200'],
            'entity_type' => ['required', 'string', 'max:200'],
            'states'      => ['required', 'array', 'min:1'],
            'states.*.name'    => ['required', 'string', 'max:100'],
            'states.*.label'   => ['nullable', 'string', 'max:200'],
            'states.*.initial' => ['nullable', 'boolean'],
            'states.*.final'   => ['nullable', 'boolean'],
            'transitions'      => ['required', 'array', 'min:1'],
            'transitions.*.from'  => ['required', 'string', 'max:100'],
            'transitions.*.to'    => ['required', 'string', 'max:100'],
            'transitions.*.event' => ['required', 'string', 'max:100'],
            'guards'    => ['nullable', 'array'],
            'actions'   => ['nullable', 'array'],
            'is_active' => ['boolean'],
            'metadata'  => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'          => 'Tenant identifier is required.',
            'tenant_id.uuid'              => 'Tenant identifier must be a valid UUID.',
            'name.required'               => 'Workflow name is required.',
            'entity_type.required'        => 'Entity type is required.',
            'states.required'             => 'At least one state is required.',
            'transitions.required'        => 'At least one transition is required.',
            'transitions.*.from.required' => 'Each transition must specify a from-state.',
            'transitions.*.to.required'   => 'Each transition must specify a to-state.',
            'transitions.*.event.required' => 'Each transition must specify a trigger event.',
        ];
    }
}
