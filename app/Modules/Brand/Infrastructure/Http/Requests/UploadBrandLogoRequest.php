<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UploadBrandLogoRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['file' => 'required|image|max:2048|mimes:jpg,jpeg,png,gif,webp']; }
}
