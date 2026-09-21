<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UploadContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, [UserRole::Admin, UserRole::Staff]);
    }

    public function rules(): array
    {
        return [
            // Только PDF: по файлу считается хеш, и он же подписывается
            // электронной подписью. Скан в jpg для этой роли не подойдет
            'file' => 'required|file|mimes:pdf|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл договора.',
            'file.mimes'    => 'Договор должен быть в формате PDF.',
            'file.max'      => 'Файл не должен превышать 10 МБ.',
        ];
    }
}