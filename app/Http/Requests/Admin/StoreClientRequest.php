<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Property;
Use App\Http\Requests\BaseFormRequest;

class StoreClientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return in_array($this->currentUser()->role, [UserRole::Admin, UserRole::Staff]);
    }

    public function rules(): array
    {
        return [
            'client_type'                 => 'required|in            : individual,legal',
            'last_name'                   => 'nullable|string|max    : 100',
            'first_name'                  => 'nullable|string|max    : 100',
            'middle_name'                 => 'nullable|string|max    : 100',
            'company_name'                => 'nullable|string|max    : 255',
            'phone'                       => 'nullable|string|max    : 20',
            'email'                       => 'nullable|email|max     : 255',
            'inn'                         => 'nullable|string|max    : 12',
            'kpp'                         => 'nullable|string|max    : 9',
            'ogrn'                        => 'nullable|string|max    : 15',
            'properties'                  => 'required|array|min     : 1',
            'properties.*.account_number' => 'required|string|max    : 20',
            'properties.*.tariff_id'      => 'required|integer|exists: tariffs,id',
            'properties.*.locality'       => 'required|string|max    : 100',
            'properties.*.street'         => 'required|string|max    : 100',
            'properties.*.house'          => 'required|string|max    : 20',
            'properties.*.region'         => 'nullable|string|max    : 100',
            'properties.*.district'       => 'nullable|string|max    : 100',
            'properties.*.building'       => 'nullable|string|max    : 20',
            'properties.*.apartment'      => 'nullable|string|max:20',
        ];
    }

    public function withValidator($validator): void
    {
        // Проверка уникальности лицевых счетов в БД — нельзя делать через
        // правило unique:properties,account_number, потому что мы проверяем
        // сразу массив и хотим указать конкретный номер в сообщении об ошибке.
        $validator->after(function ($validator) {
            foreach ($this->input('properties', []) as $prop) {
                $number = $prop['account_number'] ?? null;
                if ($number && Property::where('account_number', $number)->exists()) {
                    $validator->errors()->add(
                        'properties',
                        "Лицевой счёт {$number} уже существует в базе данных."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'properties.required'                  => 'Необходимо добавить хотя бы один объект.',
            'properties.*.account_number.required' => 'Укажите лицевой счёт для каждого объекта.',
            'properties.*.account_number.distinct' => 'Лицевые счета в заявке не должны повторяться.',
            'properties.*.tariff_id.required'      => 'Выберите тариф для каждого объекта.',
            'properties.*.locality.required'       => 'Укажите населённый пункт.',
            'properties.*.street.required'         => 'Укажите улицу.',
            'properties.*.house.required'          => 'Укажите номер дома.',
            'email.email'                          => 'Некорректный формат email.',
        ];
    }
}