<?php
declare(strict_types=1);
namespace Modules\Auth\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|same:password',
        ];
    }
}
