<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadClientFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'staff']);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл для загрузки.',
            'file.mimes'    => 'Допустимые форматы: PDF, JPG, PNG.',
            'file.max'      => 'Файл не должен превышать 10 МБ.',
        ];
    }
}