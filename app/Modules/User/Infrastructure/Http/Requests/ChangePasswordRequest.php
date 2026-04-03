<?php
declare(strict_types=1);
namespace Modules\User\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return []; }
}
