<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LogoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by VerifyJwtToken middleware
    }

    public function rules(): array
    {
        return [];
    }
}
