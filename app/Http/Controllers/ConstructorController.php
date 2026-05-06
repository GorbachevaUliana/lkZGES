<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ConstructorController extends Controller
{
    public function submit(Request $request)
    {
        // 1. Валидация данных из формы
        $validatedData = $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'address' => 'required|string',
            'phone' => 'required|string',
            // добавьте сюда остальные поля вашей формы
        ]);

        return DB::transaction(function () use ($validatedData) {
            $user = auth()->user();

            // 2. Создаем запись в таблице clients (статус applicant)
            $client = Client::create([
                'last_name' => $validatedData['last_name'],
                'first_name' => $validatedData['first_name'],
                'middle_name' => $validatedData['middle_name'] ?? null,
                'address' => $validatedData['address'],
                'phone' => $validatedData['phone'],
                'email' => $user->email,
                'status' => 'applicant', // <--- Важный момент!
                'account_number' => null, // Лицевого счета пока нет
            ]);

            // 3. Обновляем пользователя: меняем роль и привязываем client_id
            $user->update([
                'role' => 'client',
                'client_id' => $client->id,
            ]);

            // 4. Генерируем PDF
            $pdf = Pdf::loadView('pdf.application_contract', [
                'data' => $validatedData,
                'user_email' => $user->email,
                'application_id' => time(), // Временный ID для примера
            ]);

            // Сохраняем файл физически
            $fileName = 'app_'.$client->id.'_'.time().'.pdf';
            $filePath = 'applications/'.$fileName;
            Storage::disk('public')->put($filePath, $pdf->output());

            // 5. Создаем запись в таблице applications
            Application::create([
                'user_id' => $user->id,
                'template_id' => 1, // ID шаблона из вашей таблицы application_templates
                'data' => $validatedData,
                'status' => 'pending', // Ожидает обработки админом
                // 'document_path' => $filePath, // Если вы добавили такое поле в миграцию
            ]);

            // 6. ПЕРЕНАПРАВЛЕНИЕ в ЛК
            return redirect()->route('client.dashboard')
                ->with('success', 'Заявка успешно отправлена! Ваш личный кабинет активирован в режиме просмотра.');
        });
    }
}
