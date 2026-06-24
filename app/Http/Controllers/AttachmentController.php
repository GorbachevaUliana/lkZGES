<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Защищённая раздача вложений к тикетам.
     *
     * Клиент видит только вложения своих тикетов.
     * Сотрудник/админ — любые (для работы с обращениями).
     */
    public function serve(Request $request, TicketAttachment $attachment)
    {
        $user = auth()->user();

        // Клиент видит только вложения своих тикетов.
        if (! in_array($user->role, ['admin', 'staff'])) {
            $ticket = $attachment->ticket;
            if (! $ticket || $ticket->user_id !== $user->id) {
                abort(403, 'Нет доступа к этому файлу.');
            }
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'Файл не найден.');
        }

        return Storage::disk('local')->response($attachment->file_path, $attachment->file_name);
    }
}