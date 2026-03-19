<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

/**
 * Validation rules for creating a new product.
 */
final class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
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
        $types       = implode(',', Product::TYPES);
        $costMethods = implode(',', Product::COST_METHODS);
        $barcodeTypes = implode(',', Product::BARCODE_TYPES);

        return [
            'sku'              => 'required|string|max:100',
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'type'             => "required|in:{$types}",
            'status'           => 'nullable|in:active,inactive,discontinued',
            'category_id'      => 'nullable|uuid',
            'organization_id'  => 'required|uuid',
            'branch_id'        => 'nullable|uuid',
            'base_uom_id'      => 'nullable|uuid',
            'buying_uom_id'    => 'nullable|uuid',
            'selling_uom_id'   => 'nullable|uuid',
            'cost_method'      => "nullable|in:{$costMethods}",
            'barcode'          => 'nullable|string|max:100',
            'barcode_type'     => "nullable|in:{$barcodeTypes}",
            'is_serialized'    => 'nullable|boolean',
            'is_lot_tracked'   => 'nullable|boolean',
            'is_batch_tracked' => 'nullable|boolean',
            'has_expiry'       => 'nullable|boolean',
            'weight'           => 'nullable|numeric|min:0',
            'weight_unit'      => 'nullable|string|max:20',
            'dimensions'       => 'nullable|array',
            'dimensions.length' => 'nullable|numeric|min:0',
            'dimensions.width'  => 'nullable|numeric|min:0',
            'dimensions.height' => 'nullable|numeric|min:0',
            'dimensions.unit'   => 'nullable|string|max:10',
            'metadata'         => 'nullable|array',
            'images'           => 'nullable|array',
            'images.*.url'     => 'required_with:images|url',
            'images.*.alt_text'  => 'nullable|string|max:255',
            'images.*.sort_order' => 'nullable|integer|min:0',
            'images.*.is_primary' => 'nullable|boolean',
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
