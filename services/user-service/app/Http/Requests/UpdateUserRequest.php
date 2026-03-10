<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'first_name'  => ['sometimes', 'string', 'max:100'],
            'last_name'   => ['sometimes', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'address'     => ['nullable', 'array'],
            'preferences' => ['nullable', 'array'],
        ];
    }
}
