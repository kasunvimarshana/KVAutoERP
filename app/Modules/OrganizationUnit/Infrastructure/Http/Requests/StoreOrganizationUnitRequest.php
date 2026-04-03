<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationUnitRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'name' => 'required|string',
            'tenant_id' => 'required|integer',
            'code' => 'nullable|string',
            'type' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'description' => 'nullable|string',
        ];
    }
}
