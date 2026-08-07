<?php

namespace App\Http\Requests\Client;

use App\Enums\TicketCategory;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTicketRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', new Enum(TicketCategory::class)],
            'subject'  => 'required|string|max:255',
            'message'  => 'required|string|max:10000',
            'files'    => 'nullable|array|max:5',
            'files.*'  => 'file|mimes:jpg,jpeg,png,webp,pdf,doc,docx|max:15360',
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'Выберите тему обращения.',
            'subject.required'  => 'Укажите тему обращения.',
            'message.required'  => 'Опишите суть обращения.',
            'files.max'         => 'Можно прикрепить не более 5 файлов.',
            'files.*.mimes'     => 'Разрешены форматы: JPG, PNG, WEBP, PDF, DOC, DOCX.',
            'files.*.max'       => 'Размер каждого файла не должен превышать 15 МБ.',
        ];
    }
}