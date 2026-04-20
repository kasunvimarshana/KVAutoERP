<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenantId = $this->input('tenant_id');
        $productId = (int) $this->route('product');

        return [
            'tenant_id' => 'required|integer|exists:tenants,id',
            'category_id' => 'nullable|integer|exists:product_categories,id',
            'brand_id' => 'nullable|integer|exists:product_brands,id',
            'org_unit_id' => 'nullable|integer|exists:org_units,id',
            'type' => 'required|string|in:physical,service,digital,combo,variable',
            'name' => 'required|string|max:255',
            'image_path' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'slug')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'sku')
                    ->where(fn ($query) => $query->where('tenant_id', $tenantId))
                    ->ignore($productId),
            ],
            'description' => 'nullable|string',
            'base_uom_id' => 'required|integer|exists:units_of_measure,id',
            'purchase_uom_id' => 'nullable|integer|exists:units_of_measure,id',
            'sales_uom_id' => 'nullable|integer|exists:units_of_measure,id',
            'tax_group_id' => 'nullable|integer|exists:tax_groups,id',
            'uom_conversion_factor' => 'nullable|numeric|min:0.0000000001',
            'is_batch_tracked' => 'nullable|boolean',
            'is_lot_tracked' => 'nullable|boolean',
            'is_serial_tracked' => 'nullable|boolean',
            'valuation_method' => 'nullable|string|in:fifo,lifo,fefo,weighted_average,standard',
            'standard_cost' => 'nullable|numeric|min:0',
            'income_account_id' => 'nullable|integer|exists:accounts,id',
            'cogs_account_id' => 'nullable|integer|exists:accounts,id',
            'inventory_account_id' => 'nullable|integer|exists:accounts,id',
            'expense_account_id' => 'nullable|integer|exists:accounts,id',
            'is_active' => 'nullable|boolean',
            'metadata' => 'nullable|array',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $isSerialTracked = (bool) $this->input('is_serial_tracked', false);
            $isBatchTracked = (bool) $this->input('is_batch_tracked', false);
            $isLotTracked = (bool) $this->input('is_lot_tracked', false);

            if ($isSerialTracked && ($isBatchTracked || $isLotTracked)) {
                $validator->errors()->add('is_serial_tracked', 'Serial-tracked products cannot be batch-tracked or lot-tracked.');
            }

            $valuationMethod = (string) $this->input('valuation_method', 'fifo');
            $standardCost = $this->input('standard_cost');

            if ($valuationMethod === 'standard' && ($standardCost === null || $standardCost === '')) {
                $validator->errors()->add('standard_cost', 'Standard cost is required when valuation method is standard.');
            }
        });
    }
}
