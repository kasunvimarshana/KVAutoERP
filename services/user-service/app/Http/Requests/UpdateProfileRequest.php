<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'    => ['nullable', 'string', 'max:100'],
            'last_name'     => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender'        => ['nullable', 'string', 'in:male,female,non_binary,prefer_not_to_say,other'],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'address'       => ['nullable', 'string', 'max:500'],
            'city'          => ['nullable', 'string', 'max:100'],
            'country'       => ['nullable', 'string', 'max:100'],
            'timezone'      => ['nullable', 'string', 'timezone:all'],
            'language'      => ['nullable', 'string', 'max:10'],
            'preferences'   => ['nullable', 'array'],
            'metadata'      => ['nullable', 'array'],
        ];
    }
}
