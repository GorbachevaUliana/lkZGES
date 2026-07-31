<?php

namespace App\Http\Requests\Client;

use App\Http\Requests\BaseFormRequest;
use App\Enums\TicketCategory;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->currentUser()?->canCreateTickets() ?? false;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', new Enum(TicketCategory::class)],
            'subject'  => 'required|string|max:255',
            'message'  => 'required|string|max:10000',
            'files.*'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Выберите тему обращения',
            'subject.required' => 'Укажите тему обращения.',
            'message.required' => 'Введите текст обращения.',
            'message.max' => 'Текст обращения не должен превышать 10 000 символов.',
            'files.*.mimes' => 'Допустимые форматы: JPG, PNG, PDF, DOC, DOCX.',
            'files.*.max' => 'Каждый файл не должен превышать 5 МБ.',
        ];
    }
}