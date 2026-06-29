<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, [UserRole::Admin, UserRole::Staff]);
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|unique:users',
            'password'    => 'required|min:8',
            'role'        => 'required|in:admin,staff',
            'permissions' => 'nullable|array',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->role === UserRole::Admin->value && auth()->user()->role !== UserRole::Admin) {
                $validator->errors()->add('role', 'Только администратор может назначать роль администратора.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.min' => 'Пароль должен быть не менее 8 символов.',
            'role.in'      => 'Недопустимая роль.',
        ];
    }
}