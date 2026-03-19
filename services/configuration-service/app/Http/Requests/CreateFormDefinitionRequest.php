<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFormDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id'    => ['required', 'uuid'],
            'service_name' => ['required', 'string', 'max:100'],
            'entity_type'  => ['required', 'string', 'max:200'],
            'fields'       => ['required', 'array', 'min:1'],
            'fields.*.name' => ['required', 'string', 'max:100'],
            'fields.*.type' => ['required', 'string', 'in:text,textarea,number,email,date,datetime,select,multiselect,checkbox,radio,file,hidden'],
            'fields.*.label'     => ['nullable', 'string', 'max:200'],
            'fields.*.required'  => ['nullable', 'boolean'],
            'fields.*.order'     => ['nullable', 'integer', 'min:0'],
            'validations'  => ['nullable', 'array'],
            'is_active'    => ['boolean'],
            'metadata'     => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required'        => 'Tenant identifier is required.',
            'tenant_id.uuid'            => 'Tenant identifier must be a valid UUID.',
            'service_name.required'     => 'Service name is required.',
            'entity_type.required'      => 'Entity type is required.',
            'fields.required'           => 'At least one field definition is required.',
            'fields.*.name.required'    => 'Each field must have a name.',
            'fields.*.type.required'    => 'Each field must have a type.',
            'fields.*.type.in'          => 'Field type must be a supported input type.',
        ];
    }
}
