<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;    

class TicketController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Ticket::with([
            'user.client.documents',
            'staff',
            'attachments',
            'repliedBy',
        ]);

        if ($user->role !== 'admin') {
            $query->where('staff_id', $user->id);
        }

        $tickets = $query->latest()->get()->map(function ($ticket) {
            $ticket->attachments->map(function ($attachment) {
                $attachment->url = route('attachments.serve', $attachment->id);

                return $attachment;
            });

            return $ticket;
        });

        return Inertia::render('Admin/Tickets/TicketsIndex', [
            'tickets' => $tickets,
            'staff_members' => User::whereIn('role', ['staff', 'admin'])
                ->where(function ($query) {
                    $query->where('role', 'admin')
                        ->orWhereJsonContains('permissions', 'tickets');
                })
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        // IDOR-защита: сотрудник (не admin) может изменять только тикеты,
        // назначенные на него. Без этой проверки любой staff мог отправить
        // PUT на /admin/tickets/{любой_id} и изменить чужой тикет.
        $user = auth()->user();
        if ($user->role !== 'admin' && $ticket->staff_id !== $user->id) {
            abort(403, 'Вы можете изменять только обращения, назначенные вам.');
        }

        $request->validate([
            // Явный список допустимых статусов — раньше принималась любая строка.
            // 'new' — начальный статус при создании (см. миграцию).
            'status'        => 'required|string|in:new,pending,closed',
            // Назначать можно только реальных сотрудников с доступом к тикетам,
            // а не любого пользователя из таблицы users.
            'staff_id'      => [
                'nullable',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value === null) return;
                    $assignee = User::find($value);
                    if (!$assignee) return;
                    $isStaff = in_array($assignee->role, ['admin', 'staff']);
                    $hasTicketsAccess = $assignee->role === 'admin'
                        || (is_array($assignee->permissions) && in_array('tickets', $assignee->permissions));
                    if (!$isStaff || !$hasTicketsAccess) {
                        $fail('Нельзя назначить тикет на пользователя без доступа к обращениям.');
                    }
                },
            ],
            'admin_reply'   => 'nullable|string|max:10000',
            'admin_files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        // Обновляем основные поля через массовое присвоение.
        // replied_by НЕ входит в $fillable модели Ticket, поэтому обновляем
        // его отдельно через whereKey()->update() — иначе поле молча игнорируется
        // и на фронте никогда не показывается "кто ответил".
        $ticket->update([
            'status'      => $request->status,
            'staff_id'    => $request->staff_id,
            'admin_reply' => $request->admin_reply,
            'replied_at'  => $request->admin_reply ? now() : $ticket->replied_at,
        ]);

        if ($request->admin_reply) {
            Ticket::whereKey($ticket->id)->update(['replied_by' => auth()->id()]);
        }

        if ($request->hasFile('admin_files')) {
            foreach ($request->file('admin_files') as $file) {
                $path = $file->store('tickets/replies', 'local');
                $ticket->attachments()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'is_admin' => true,
                ]);
            }
        }

        return back();
    }
}