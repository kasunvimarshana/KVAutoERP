<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\UnitOfMeasure;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use KvEnterprise\SharedKernel\Http\Responses\ApiResponse;

/**
 * Validation rules for creating a Unit of Measure.
 */
final class StoreUomRequest extends FormRequest
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
        $categories = implode(',', UnitOfMeasure::CATEGORIES);

        return [
            'name'         => 'required|string|max:100',
            'symbol'       => 'required|string|max:20',
            'category'     => "required|in:{$categories}",
            'is_base_unit' => 'nullable|boolean',
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
