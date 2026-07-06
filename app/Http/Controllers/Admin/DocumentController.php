<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Models\Document;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Защищённая раздача документов.
     *
     * Клиент видит только свои документы.
     * Сотрудник/админ — любые (для работы с заявками).
     * Файл отдаётся потоком с правильным Content-Type —
     * браузер откроет PDF/изображение во вкладке, остальное скачает.
     */
    public function serve(Request $request, Document $document)
    {
        $user = auth()->user();

        // Клиент видит только документы своего клиентского профиля.
        if (
            $user->role === UserRole::Client || 
            $user->role === UserRole::Guest || 
            $user->role === UserRole::Applicant
            ) {
            $client = $user->client;
            if (! $client || $document->client_id !== $client->id) {
                abort(403, 'Нет доступа к этому документу.');
            }
        }
        // Сотрудники и администраторы имеют доступ ко всем документам —
        // они работают с заявками и договорами клиентов.

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Файл не найден.');
        }

        return Storage::disk('local')->response($document->file_path, $document->name);
    }

    /**
     * Удаление документа (только для сотрудников).
     * Метод уже используется в роуте admin.documents.destroy.
     */
    public function destroy(Document $document)
    {
        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return back();
    }
}