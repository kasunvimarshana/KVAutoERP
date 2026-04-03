<?php
declare(strict_types=1);
namespace Modules\Brand\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name' => 'sometimes|required|string|max:255', 'slug' => 'sometimes|required|string|max:255', 'description' => 'nullable|string', 'website' => 'nullable|url', 'status' => 'nullable|string|in:active,inactive']; }
}
