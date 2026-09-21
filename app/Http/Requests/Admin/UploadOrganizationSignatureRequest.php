<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UploadOrganizationSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()->role, [UserRole::Admin, UserRole::Staff], true);
    }

    public function rules(): array
    {
        return [
            // Открепленная подпись: .sig (КриптоАРМ) или .p7s (КриптоПро).
            // Стандартного MIME-типа у таких файлов нет, поэтому
            // проверяем расширение, а не содержимое.
            'file' => 'required|file|extensions:sig,p7s|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'   => 'Выберите файл подписи.',
            'file.extensions' => 'Файл подписи должен иметь расширение .sig или .p7s.',
            'file.max'        => 'Файл подписи не должен превышать 1 МБ.',
        ];
    }
}