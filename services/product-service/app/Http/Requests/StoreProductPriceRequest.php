<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ProductPrice;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Validation rules for adding a product price.
 */
final class StoreProductPriceRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $priceTypes = implode(',', ProductPrice::PRICE_TYPES);

        return [
            'currency_code' => 'required|string|size:3',
            'price_type'    => "required|in:{$priceTypes}",
            'price'         => 'required|numeric|min:0',
            'tier_min_qty'  => 'nullable|numeric|min:0|required_if:price_type,tier',
            'valid_from'    => 'nullable|date',
            'valid_to'      => 'nullable|date|after_or_equal:valid_from',
            'location_id'   => 'nullable|uuid',
        ];
    }

    /**
     * @param  Validator  $validator
     * @return void
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray()),
        );
    }
}
