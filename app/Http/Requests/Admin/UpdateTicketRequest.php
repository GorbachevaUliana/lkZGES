<?php

namespace App\Http\Requests\Admin;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket') instanceof Ticket
            ? $this->route('ticket')
            : Ticket::findOrFail($this->route('ticket') ?? $this->route('id'));

        $user = auth()->user();

        // Сотрудник может изменять только назначенные ему тикеты.
        if ($user->role !== 'admin' && $ticket->staff_id !== $user->id) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'status'        => 'required|string|in:new,pending,closed',
            'staff_id'      => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value === null) return;
                    $assignee = User::find($value);
                    if (! $assignee) return;
                    $isStaff = in_array($assignee->role, ['admin', 'staff']);
                    $hasAccess = $assignee->role === 'admin'
                        || (is_array($assignee->permissions) && in_array('tickets', $assignee->permissions));
                    if (! $isStaff || ! $hasAccess) {
                        $fail('Нельзя назначить тикет на пользователя без доступа к обращениям.');
                    }
                },
            ],
            'admin_reply'   => 'nullable|string|max:10000',
            'admin_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'           => 'Недопустимый статус обращения.',
            'admin_reply.max'     => 'Ответ не должен превышать 10 000 символов.',
            'admin_files.*.mimes' => 'Допустимые форматы: jpg, jpeg, png, pdf, doc, docx.',
            'admin_files.*.max'   => 'Каждый файл не должен превышать 10 МБ.',
        ];
    }
}