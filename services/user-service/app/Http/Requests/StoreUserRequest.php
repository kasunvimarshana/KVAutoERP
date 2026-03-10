<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'auth_user_id' => ['required', 'string'],
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'address'      => ['nullable', 'array'],
            'preferences'  => ['nullable', 'array'],
        ];
    }
}
