<?php
declare(strict_types=1);
namespace Modules\OrganizationUnit\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class UploadOrganizationUnitAttachmentRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'file' => 'nullable|file',
            'files' => 'nullable|array',
            'files.*' => 'file',
            'type' => 'nullable|string',
        ];
    }
    public function withValidator($validator): void {}
}
