<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreOrderRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['items'=>['required','array','min:1'],'items.*.product_id'=>['required','string'],'items.*.quantity'=>['required','integer','min:1'],'items.*.unit_price'=>['nullable','numeric','min:0'],'items.*.discount'=>['nullable','numeric','min:0'],'tax'=>['nullable','numeric','min:0'],'discount'=>['nullable','numeric','min:0'],'shipping_address'=>['nullable','array'],'notes'=>['nullable','string']]; }
}
