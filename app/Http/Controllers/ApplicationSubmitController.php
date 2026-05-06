<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use App\Models\PdfTemplate;
use App\Models\Property;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ApplicationSubmitController extends Controller
{
    public function show(Request $request, $slug)
    {
        $template = ApplicationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return Inertia::render('Applications/DynamicForm', [
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'slug' => $template->slug,
                'content' => $template->content,
            ],
            'clientTypes' => Client::getClientTypes(),
        ]);
    }

    public function submit(Request $request, $slug)
    {
        $template = ApplicationTemplate::where('slug', $slug)->firstOrFail();
        $clientType = $request->input('client_type', 'individual');
        
        $validatedData = $this->normalizeData($request->all()); 
        $validatedData['client_type'] = $clientType;

        return DB::transaction(function () use ($validatedData, $template, $clientType, $request) {
            $user = auth()->user();

            // 1. Клиент
            $client = $this->createClient($validatedData, $user);

            // 2. Объект (Property)
            $property = Property::create([
                'client_id' => $client->id,
                'address' => $validatedData['address'] ?? $validatedData['Адрес'] ?? 'Не указано',
                'status' => 'pending',
            ]);

            // 3. Роль
            $user->update(['role' => 'applicant']);

            // 4. Сначала создаем заявку (пустая ссылка на PDF пока что)
            $application = Application::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'property_id' => $property->id,
                'template_id' => $template->id,
                'client_type' => $clientType,
                'data' => $validatedData,
                'status' => 'pending',
                'generated_pdf_path' => '', // Заполним через минуту
            ]);

            // 5. Генерируем PDF, передавая и заявку, и объект недвижимости
            $pdfPath = $this->generatePdf($validatedData, $client, $clientType, $application, $property);

            // 6. Обновляем путь к PDF в заявке
            $application->update(['generated_pdf_path' => $pdfPath]);

            // 7. Регистрируем документ
            Document::create([
                'client_id' => $client->id,
                'application_id' => $application->id,
                'name' => "Заявка №{$application->id} от " . now()->format('d.m.Y'),
                'file_path' => $pdfPath,
                'type' => 'application_pdf',
                'description' => 'Автоматическая генерация заявки',
            ]);

            $this->handleUploadedFiles($request, $client, $application->id);

            return redirect()->route('client.dashboard')
                ->with('success', 'Заявка успешно отправлена!');
        });
    }
    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && isset($value['preset'])) {
                $presets = $value['preset'] ?? [];
                $customs = collect($value['custom'] ?? [])
                    ->pluck('value')
                    ->filter()
                    ->toArray();

                $normalized[$key] = implode(', ', array_merge($presets, $customs));

                continue;
            }

            if (is_array($value) && isset($value['value'])) {
                $normalized[$key] = ($value['value'] === 'other')
                    ? ($value['customValue'] ?? 'Не указано')
                    : $value['value'];

                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    private function createClient(array $data, $user): Client
    {
        return Client::updateOrCreate(
            ['user_id' => $user->id],
            [
                'client_type' => $data['client_type'] ?? 'individual',
                'last_name' => $data['last_name'] ?? $data['Фамилия'] ?? 'Не указано',
                'first_name' => $data['first_name'] ?? $data['Имя'] ?? 'Не указано',
                'middle_name' => $data['middle_name'] ?? $data['Отчество'] ?? '',
                // 'address' => $data['address'] ?? $data['Адрес'] ?? 'Не указано',
                'phone' => $data['phone'] ?? $data['Телефон'] ?? 'Не указано',
                'company_name' => $data['company_name'] ?? $data['Наименование организации'] ?? null,
                'inn' => $data['inn'] ?? $data['ИНН'] ?? null,
            ]
        );
    }

    private function generatePdf(array $data, Client $client, string $clientType, $application, $property): string
    {
        $pdfTemplate = PdfTemplate::getTemplate($clientType, PdfTemplate::DOC_APPLICATION);

        $mainInfo = [
            'application_id' => $application->id, // Теперь тут реальный номер заявки
            'full_name' => "{$client->last_name} {$client->first_name} {$client->middle_name}",
            'user_email' => auth()->user()->email,
            'created_at' => now()->format('d.m.Y H:i'),
            'client_type_name' => Client::getClientTypes()[$clientType] ?? $clientType,
            'address' => $property->address, // Берем адрес из объекта недвижимости
            'phone' => $client->phone,
        ];

        $excludeKeys = [
            'last_name', 'first_name', 'middle_name', 'address', 'phone',
            'client_type', 'Фамилия', 'Имя', 'Отчество', 'Адрес', 'Телефон',
        ];
        $extraData = array_diff_key($data, array_flip($excludeKeys));

        $templateData = array_merge($extraData, $mainInfo);

        if ($pdfTemplate) {
            $htmlContent = $pdfTemplate->render($templateData);
        } else {
            $viewName = $clientType === 'legal' ? 'pdf.application_legal' : 'pdf.application_individual';
            $htmlContent = view($viewName, ['data' => $templateData])->render();
        }

        $pdf = Pdf::loadHTML($htmlContent)->setPaper('a4');
        
        // Имя файла теперь тоже может содержать ID заявки для удобства
        $fileName = 'app_no_'.$application->id.'_'.time().'.pdf';
        $filePath = 'applications/'.$fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    private function handleUploadedFiles(Request $request, Client $client, $appId): void
    {
        foreach ($request->allFiles() as $file) {
            if ($file && $file->isValid()) {
                $path = $file->store('client_documents', 'public');
                Document::create([
                    'client_id' => $client->id,
                    'application_id' => $appId,
                    'name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'type' => Document::TYPE_OTHER,
                ]);
            }
        }
    }
}
