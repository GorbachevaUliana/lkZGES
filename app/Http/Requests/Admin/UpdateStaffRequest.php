<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')?->id ?? $this->route('staff');

        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $staffId,
            'password'    => 'nullable|min:8',
            'role'        => 'required|in:admin,staff',
            'permissions' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.min' => 'Пароль должен быть не менее 8 символов.',
        ];
    }
}