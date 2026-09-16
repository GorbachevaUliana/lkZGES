<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class PublishContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, [UserRole::Admin, UserRole::Staff], true);
    }

    /**
     * Ничего не передаётся — договор берётся из маршрута.
     * Класс существует ради проверки прав.
     */
    public function rules(): array
    {
        return [];
    }
}