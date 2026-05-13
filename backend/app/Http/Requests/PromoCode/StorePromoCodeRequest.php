<?php

namespace App\Http\Requests\PromoCode;

use Illuminate\Foundation\Http\FormRequest;

class StorePromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:promo_codes,code',
            'discount_percent' => 'required|integer|min:1|max:100',
            'is_active' => 'sometimes|boolean',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'duration_ids' => 'nullable|array',
            'duration_ids.*' => 'exists:durations,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Код промокода обязателен для заполнения',
            'code.unique' => 'Промокод с таким кодом уже существует',
            'code.max' => 'Код промокода не должен превышать 20 символов',
            'discount_percent.required' => 'Процент скидки обязателен для заполнения',
            'discount_percent.integer' => 'Процент скидки должен быть целым числом',
            'discount_percent.min' => 'Процент скидки должен быть не менее 1',
            'discount_percent.max' => 'Процент скидки должен быть не более 100',
            'usage_limit.integer' => 'Лимит использований должен быть целым числом',
            'usage_limit.min' => 'Лимит использований должен быть не менее 1',
            'expires_at.date' => 'Дата истечения должна быть корректной датой',
            'expires_at.after' => 'Дата истечения должна быть в будущем',
        ];
    }
}

