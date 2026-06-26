<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class LinkAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'account_number' => 'required|string|max:50',
            'last_name'      => 'required|string|max:100',
            'first_name'     => 'required|string|max:100',
            'middle_name'    => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Введите номер лицевого счёта.',
            'last_name.required'      => 'Введите фамилию.',
            'first_name.required'     => 'Введите имя.',
            'middle_name.required'    => 'Введите отчество.',
        ];
    }
}