<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Http\Requests\BaseFormRequest;

class UpdateClientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return in_array($this->currentUser()->role, [ UserRole::Admin, UserRole::Staff]);
    }

    public function rules(): array
    {
        return [
            'client_type'  => 'required|in:individual,legal',
            'last_name'    => 'nullable|string|max:100',
            'first_name'   => 'nullable|string|max:100',
            'middle_name'  => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'client_type.in' => 'Недопустимый тип клиента.',
            'email.email'    => 'Некорректный формат email.',
        ];
    }
}