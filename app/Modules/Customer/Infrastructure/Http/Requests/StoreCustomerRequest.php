<?php declare(strict_types=1);
namespace Modules\Customer\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreCustomerRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['tenant_id'=>'required|integer','name'=>'required|string','code'=>'required|string','status'=>'nullable|string','type'=>'nullable|string','loyalty_tier'=>'nullable|string','credit_limit'=>'nullable|numeric']; }
}
