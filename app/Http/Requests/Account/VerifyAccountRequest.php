<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class VerifyAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|size:6',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Введите код из письма.',
            'code.size'     => 'Код должен состоять ровно из 6 цифр.',
        ];
    }
}