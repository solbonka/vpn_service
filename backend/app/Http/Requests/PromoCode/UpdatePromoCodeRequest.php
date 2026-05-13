<?php

namespace App\Http\Requests\PromoCode;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $promoCodeId = $this->route('id');

        return [
            'code' => 'sometimes|string|max:20|unique:promo_codes,code,' . $promoCodeId,
            'discount_percent' => 'sometimes|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'duration_ids' => 'nullable|array',
            'duration_ids.*' => 'exists:durations,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Промокод с таким кодом уже существует',
            'code.max' => 'Код промокода не должен превышать 20 символов',
            'discount_percent.integer' => 'Процент скидки должен быть целым числом',
            'discount_percent.min' => 'Процент скидки должен быть не менее 1',
            'discount_percent.max' => 'Процент скидки должен быть не более 100',
            'usage_limit.integer' => 'Лимит использований должен быть целым числом',
            'usage_limit.min' => 'Лимит использований должен быть не менее 1',
            'expires_at.date' => 'Дата истечения должна быть корректной датой',
        ];
    }
}

