<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateOrderRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['notes'=>['nullable','string'],'shipping_address'=>['nullable','array']]; }
}
