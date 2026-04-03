<?php
declare(strict_types=1);
namespace Modules\Tenant\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string',
            'domain' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'database_config' => 'nullable|array',
        ];
    }
}
