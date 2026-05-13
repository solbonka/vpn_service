<?php

namespace App\Http\Requests\Charts;

use Illuminate\Foundation\Http\FormRequest;

class ChartDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'period' => 'sometimes|string|in:7d,30d,90d,custom',
            'start_date' => 'required_if:period,custom|nullable|date|before_or_equal:end_date',
            'end_date' => 'required_if:period,custom|nullable|date|after_or_equal:start_date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'period.in' => 'Период должен быть одним из: 7d, 30d, 90d, custom',
            'start_date.required_if' => 'Начальная дата обязательна при выборе custom периода',
            'end_date.required_if' => 'Конечная дата обязательна при выборе custom периода',
            'start_date.before_or_equal' => 'Начальная дата должна быть раньше или равна конечной дате',
            'end_date.after_or_equal' => 'Конечная дата должна быть позже или равна начальной дате',
            'end_date.before_or_equal' => 'Конечная дата не может быть в будущем',
        ];
    }
}
