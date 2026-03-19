<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_name' => ['sometimes', 'string', 'max:100'],
            'entity_type'  => ['sometimes', 'string', 'max:200'],
            'fields'       => ['sometimes', 'array', 'min:1'],
            'fields.*.name' => ['required_with:fields', 'string', 'max:100'],
            'fields.*.type' => ['required_with:fields', 'string', 'in:text,textarea,number,email,date,datetime,select,multiselect,checkbox,radio,file,hidden'],
            'fields.*.label'    => ['nullable', 'string', 'max:200'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.order'    => ['nullable', 'integer', 'min:0'],
            'validations' => ['nullable', 'array'],
            'is_active'   => ['boolean'],
            'metadata'    => ['nullable', 'array'],
        ];
    }
}
