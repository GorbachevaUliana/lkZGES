<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->canCreateTickets() ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Укажите тему обращения.',
            'message.required' => 'Введите текст обращения.',
            'message.max' => 'Текст обращения не должен превышать 10 000 символов.',
            'files.*.mimes' => 'Допустимые форматы: JPG, PNG, PDF, DOC, DOCX.',
            'files.*.max' => 'Каждый файл не должен превышать 5 МБ.',
        ];
    }
}