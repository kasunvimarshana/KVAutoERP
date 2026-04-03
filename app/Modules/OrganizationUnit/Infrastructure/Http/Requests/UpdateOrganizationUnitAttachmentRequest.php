<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationUnitAttachmentRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'type' => 'nullable|string',
            'metadata' => 'nullable|array',
        ];
    }
    public function withValidator($validator): void {}
}
