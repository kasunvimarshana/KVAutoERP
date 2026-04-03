<?php declare(strict_types=1);
namespace Modules\Supplier\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreSupplierRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return ['tenant_id'=>'required|integer','name'=>'required|string','code'=>'required|string','status'=>'nullable|string','type'=>'nullable|string']; }
}
