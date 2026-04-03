<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'sometimes|string',
            'domain' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
        ];
    }
}
