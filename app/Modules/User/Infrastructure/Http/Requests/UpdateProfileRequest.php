<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Requests;

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
            'name'     => ['sometimes', 'string', 'max:255'],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
            'locale'   => ['sometimes', 'string', 'max:10'],
        ];
    }
}
