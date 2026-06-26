<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadApplicationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, ['admin', 'staff']);
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'name' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл для загрузки.',
            'file.mimes'    => 'Допустимые форматы: PDF, JPG, PNG, DOC, DOCX.',
            'file.max'      => 'Файл не должен превышать 10 МБ.',
        ];
    }
}