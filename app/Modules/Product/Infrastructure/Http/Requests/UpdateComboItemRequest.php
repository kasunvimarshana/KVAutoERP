<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateComboItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['quantity' => 'sometimes|required|numeric|min:0.001']; }
}
