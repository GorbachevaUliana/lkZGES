<?php

namespace App\Services;

use App\Enums\ClientType;
use App\Enums\PdfDocumentType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use App\Models\PdfTemplate;
use App\Models\Property;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationSubmitService
{
    public function __construct(
        private FileUploadService $fileUploadService,
        private PdfDataPreparator $pdfDataPreparator,
    ) {}

    /**
     * Основной метод — принимает запрос и шаблон, делает всё остальное.
     */
    public function handle(Request $request, ApplicationTemplate $template, User $user): RedirectResponse
    {
        // Валидация файлов ДО транзакции — если упала, ничего не создаётся
        $this->fileUploadService->validateFileUploads($request, $template);

        $clientType     = $this->resolveClientType($template);
        $normalizedData = $this->normalizeData($request);

        return DB::transaction(function () use ($request, $template, $user, $clientType, $normalizedData) {
            $client      = $this->resolveClient($normalizedData, $user);
            $fullAddress = $this->buildFullAddress($normalizedData);

            $property = Property::create([
                'client_id' => $client->id,
                'address'   => $fullAddress,
                'status'    => 'pending',
            ]);

            $this->promoteGuestToApplicant($user);

            $application = Application::create([
                'user_id'            => $user->id,
                'client_id'          => $client->id,
                'property_id'        => $property->id,
                'template_id'        => $template->id,
                'client_type'        => $clientType,
                'data'               => $normalizedData,
                'status'             => 'pending',
                'generated_pdf_path' => '',
            ]);

            $pdfPath = $this->generateApplicationPdf($normalizedData, $client, $clientType, $application, $property);

            $application->update(['generated_pdf_path' => $pdfPath]);

            Document::create([
                'client_id'      => $client->id,
                'application_id' => $application->id,
                'name'           => "Заявка №{$application->id} от " . now()->format('d.m.Y'),
                'file_path'      => $pdfPath,
                'type'           => PdfDocumentType::Application->value,
                'description'    => 'Автоматическая генерация заявки',
            ]);

            $this->fileUploadService->handleUploadedFiles($request, $client, $application->id, $template);

            return redirect()->route('client.dashboard')
                ->with('success', 'Заявка успешно отправлена!');
        });
    }

    /**
     * Тип клиента теперь берётся из самого шаблона, а не из запроса —
     * каждый шаблон (application-individual / application-legal)
     * соответствует ровно одному типу клиента.
     */
    private function resolveClientType(ApplicationTemplate $template): string
    {
        return $template->client_type ?? ClientType::Individual->value;
    }

    /**
     * Нормализация данных формы.
     * Чекбоксы, селекты и динамические списки приводятся к строкам.
     * Файлы пропускаются — они обрабатываются отдельно в FileUploadService.
     */
    private function normalizeData(Request $request): array
    {
        $normalized = [];

        foreach ($request->all() as $key => $value) {
            if ($request->hasFile($key)) {
                continue;
            }

            // Чекбоксы: { preset: [...], custom: [...] }
            if (is_array($value) && isset($value['preset'])) {
                $presets = $value['preset'] ?? [];
                $customs = collect($value['custom'] ?? [])
                    ->pluck('value')
                    ->filter()
                    ->toArray();

                $normalized[$key] = implode(', ', array_merge($presets, $customs));
                continue;
            }

            // Селекты: { value: '...', customValue: '...' }
            if (is_array($value) && isset($value['value'])) {
                $normalized[$key] = $value['value'] === 'other'
                    ? ($value['customValue'] ?? 'Не указано')
                    : $value['value'];
                continue;
            }

            // Динамические списки: { selected: '...', inputValue: '...' }
            if (is_array($value) && isset($value['selected'])) {
                $selected   = $value['selected']   ?? '';
                $inputValue = $value['inputValue'] ?? '';

                $normalized[$key] = ($selected && $inputValue)
                    ? "{$selected}: {$inputValue}"
                    : ($selected ?: 'Не указано');
                continue;
            }

            // Массивы File-объектов — пропускаем
            if (is_array($value) && isset($value[0]) && is_object($value[0] ?? null)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * Находит существующего клиента пользователя или создаёт нового.
     */
    private function resolveClient(array $data, User $user): Client
    {
        $existing = Client::where('user_id', $user->id)->first();

        if ($existing) {
            return $existing;
        }

        return Client::updateOrCreate(
            ['user_id' => $user->id],
            [
                'client_type'  => $data['client_type']              ?? ClientType::Individual->value,
                'last_name'    => $data['last_name']   ?? $data['Фамилия']               ?? 'Не указано',
                'first_name'   => $data['first_name']  ?? $data['Имя']                   ?? 'Не указано',
                'middle_name'  => $data['middle_name'] ?? $data['Отчество']              ?? '',
                'phone'        => $data['phone']        ?? $data['Телефон']
                                ?? $data['Контактный телефон']                           ?? null,
                'email'        => $user->email,
                'company_name' => $data['company_name'] ?? $data['Наименование организации'] ?? null,
                'inn'          => $data['inn']           ?? $data['ИНН']                  ?? null,
                'kpp'          => $data['kpp']           ?? null,
                'ogrn'         => $data['ogrn']          ?? null,
            ]
        );
    }

    /**
     * Повышает роль гостя до заявителя после подачи первой заявки.
     */
    private function promoteGuestToApplicant(User $user): void
    {
        if ($user->role === UserRole::Guest) {
            $user->forceFill(['role' => UserRole::Applicant->value])->save();
        }
    }

    /**
     * Сборка полного адреса из составных полей формы.
     * Поддерживает как латинские ключи (snake_case), так и русские названия полей,
     * а также объект энергоснабжения юрлица (object_address).
     */
    private function buildFullAddress(array $data): string
    {
        if (! empty($data['address']))        return $data['address'];
        if (! empty($data['Адрес']))          return $data['Адрес'];
        if (! empty($data['object_address'])) return $data['object_address'];

        $parts = [];
         
        

        $region = $data['region'] ?? $data['Регион'] ?? null;
        if (! empty($region)) $parts[] = $region;

        $district = $data['district'] ?? $data['Район'] ?? null;
        if (! empty($district)) $parts[] = $district;

        $locality = $data['locality'] ?? $data['Населенный пункт'] ?? $data['Город'] ?? $data['city'] ?? null;
        if (! empty($locality)) $parts[] = $locality;

        $street = $data['street'] ?? $data['Улица'] ?? null;
        if (! empty($street)) $parts[] = 'ул. ' . $street;

        $house = $data['house'] ?? $data['Дом'] ?? null;
        if (! empty($house)) $parts[] = 'д. ' . $house;

        $building = $data['corpus'] ?? $data['Корпус'] ?? $data['building'] ?? null;
        if (! empty($building)) $parts[] = 'корп. ' . $building;

        $apartment = $data['apartment'] ?? $data['Квартира'] ?? null;
        if (! empty($apartment)) $parts[] = 'кв. ' . $apartment;

        return empty($parts) ? 'Адрес не указан' : implode(', ', $parts);
    }

    /**
     * Генерирует PDF заявки и сохраняет на local диск.
     * Сначала пытается использовать шаблон из базы (PdfTemplate, Twig),
     * при отсутствии — Blade-шаблон.
     */
    private function generateApplicationPdf(
        array $data,
        Client $client,
        string $clientType,
        Application $application,
        Property $property
    ): string {
        $pdfTemplate = PdfTemplate::getTemplate($clientType, PdfDocumentType::Application->value);

        $mainInfo = [
            'application_id'   => $application->id,
            'full_name'        => trim("{$client->last_name} {$client->first_name} {$client->middle_name}"),
            'user_email'       => auth()->user()->email,
            'created_at'       => now()->format('d.m.Y H:i'),
            'client_type_name' => Client::getClientTypes()[$clientType] ?? $clientType,
            'address'          => $property->address,
            'phone'            => $client->phone       ?? 'Не указан',
            'company_name'     => $client->company_name ?? null,
            'inn'              => $client->inn           ?? null,
        ];

        $templateData = $this->pdfDataPreparator->prepare($data, $mainInfo);

        $htmlContent = $pdfTemplate
            ? $pdfTemplate->render($templateData)
            : view(
                $clientType === ClientType::Legal->value
                    ? 'pdf.application_legal'
                    : 'pdf.application_individual',
                ['data' => $templateData]
            )->render();

        $pdf      = Pdf::loadHTML($htmlContent)->setPaper('a4');
        $fileName = 'app_no_' . $application->id . '_' . time() . '.pdf';
        $filePath = 'applications/' . $fileName;

        Storage::disk('local')->put($filePath, $pdf->output());

        return $filePath;
    }
}