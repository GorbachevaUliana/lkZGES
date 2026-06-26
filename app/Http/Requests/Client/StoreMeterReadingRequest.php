<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeterReadingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'client';
    }

    public function rules(): array
    {
        return [
            'current_value' => 'required|integer|min:0',
            'reading_date'  => 'required|date',
            'property_id'   => 'required|integer|exists:properties,id',
        ];
    }

    public function messages(): array
    {
        return [
            'current_value.required' => 'Введите показания счётчика.',
            'current_value.integer'  => 'Показания должны быть целым числом.',
            'current_value.min'      => 'Показания не могут быть отрицательными.',
            'reading_date.required'  => 'Укажите дату снятия показаний.',
            'reading_date.date'      => 'Некорректный формат даты.',
            'property_id.required'   => 'Выберите объект.',
            'property_id.exists'     => 'Объект не найден.',
        ];
    }
}