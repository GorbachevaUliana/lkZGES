<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use App\Models\PdfTemplate;
use App\Models\Property;
use App\Services\PdfDataPreparator;
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

        $user = auth()->user();
        $existingClient = Client::where('user_id', $user->id)->first();

        $existingClientType = $existingClient?->client_type;
        $hasExistingClient = !empty($existingClientType);

        return Inertia::render('Applications/DynamicForm', [
            'template' => [
                'id' => $template->id,
                'title' => $template->title,
                'slug' => $template->slug,
                'content' => $template->content,
            ],
            'clientTypes' => Client::getClientTypes(),
            'existingClientType' => $existingClientType,
            'hasExistingClient' => $hasExistingClient,
        ]);
    }

    public function submit(Request $request, $slug)
    {
        $template = ApplicationTemplate::where('slug', $slug)->firstOrFail();

        // Серверная валидация файлов — до транзакции, чтобы при ошибке
        // ничего не сохранялось в БД и хранилище.
        // Правила строятся динамически из конфига шаблона (те же поля
        // accepted_types / max_size / max_files, что читает фронтенд).
        $this->validateFileUploads($request, $template);

        $clientType = $request->input('client_type', 'individual');

        $user = auth()->user();
        $existingClient = Client::where('user_id', $user->id)->first();

        if ($existingClient) {
            $clientType = $existingClient->client_type;
        }

        $validatedData = $this->normalizeData($request->all(), $request);

        return DB::transaction(function () use ($validatedData, $template, $clientType, $request, $user, $existingClient) {
            $client = $this->createClient($validatedData, $user, $existingClient);

            $fullAddress = $this->buildFullAddress($validatedData);

            $property = Property::create([
                'client_id' => $client->id,
                'address' => $fullAddress,
                'status' => 'pending',
            ]);

            // При регистрации роль выставляется 'guest' (см. RegisteredUserController).
            // Раньше тут стояло 'user' — такой роли в системе нет, поэтому
            // переход в 'applicant' никогда не срабатывал.
            if ($user->role === 'guest') {
                $user->update(['role' => 'applicant']);
            }

            $application = Application::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'property_id' => $property->id,
                'template_id' => $template->id,
                'client_type' => $clientType,
                'data' => $validatedData,
                'status' => 'pending',
                'generated_pdf_path' => '',
            ]);

            $pdfPath = $this->generatePdf($validatedData, $client, $clientType, $application, $property);
            $application->update(['generated_pdf_path' => $pdfPath]);

            Document::create([
                'client_id' => $client->id,
                'application_id' => $application->id,
                'name' => "Заявка №{$application->id} от " . now()->format('d.m.Y'),
                'file_path' => $pdfPath,
                'type' => 'application_pdf',
                'description' => 'Автоматическая генерация заявки',
            ]);

            // Обработка файлов из конструктора
            $this->handleUploadedFilesFromForm($request, $client, $application->id, $template);

            return redirect()->route('client.dashboard')
                ->with('success', 'Заявка успешно отправлена!');
        });
    }

    /**
     * Нормализация данных формы с обработкой файлов
     */
    private function normalizeData(array $data, Request $request): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            // Пропускаем файлы - они обрабатываются отдельно
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
                $normalized[$key] = ($value['value'] === 'other')
                    ? ($value['customValue'] ?? 'Не указано')
                    : $value['value'];
                continue;
            }

            //Динамические списки
            if(is_array($value) && isset($value['selected'])) {
                $selected = $value['selected'] ?? '';
                $inputValue = $value['inputValue'] ?? '';

                if ($selected && $inputValue) {
                    $normalized[$key] = $selected . ': ' . $inputValue;
                } else {
                    $normalized[$key] = $selected? : 'Не указано';
                }
                continue;
            }

            // Массивы файлов (объекты File) пропускаем
            if (is_array($value) && isset($value[0]) && is_object($value[0] ?? null)) {
                continue;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }

    /**
     * Серверная валидация файлов из полей конструктора.
     *
     * Правила строятся из конфига каждого блока file_upload в шаблоне:
     * — accepted_types  { "pdf": true, "jpg": true, ... }  → mimes:pdf,jpg,...
     * — max_size        (МБ)                                → max:<КБ>
     * — max_files       (шт.)                               → array|max:X для множественных
     * — is_required                                         → required / nullable
     *
     * Вызывается ДО транзакции: если валидация упала — ни файлы, ни запись
     * в БД не создаются, Inertia автоматически вернёт ошибки на фронт.
     */
    private function validateFileUploads(Request $request, ApplicationTemplate $template): void
    {
        $rules    = [];
        $messages = [];

        foreach ($template->content ?? [] as $block) {
            if ($block['type'] !== 'file_upload') {
                continue;
            }

            $key = $block['data']['key'] ?? $block['data']['label'] ?? null;
            if (!$key) {
                continue;
            }

            $label         = $block['data']['label'] ?? $key;
            $isRequired    = (bool) ($block['data']['is_required']   ?? false);
            $acceptedTypes = (array) ($block['data']['accepted_types'] ?? []);
            $maxSizeMb     = (int)  ($block['data']['max_size']       ?? 10);
            $maxFiles      = (int)  ($block['data']['max_files']      ?? 5);
            $allowMultiple = (bool) ($block['data']['allow_multiple'] ?? false);

            // Расширения, для которых явно стоит true (остальные — отключены в конструкторе)
            $extensions = array_keys(array_filter($acceptedTypes));
            $maxSizeKb  = $maxSizeMb * 1024;

            if ($allowMultiple) {
                // Сначала валидируем сам массив (количество файлов)
                $arrayRules   = $isRequired ? ['required', 'array'] : ['nullable', 'array'];
                $arrayRules[] = "max:{$maxFiles}";
                $rules[$key]  = $arrayRules;

                if ($isRequired) {
                    $messages["{$key}.required"] = "Поле «{$label}» обязательно для заполнения.";
                }
                $messages["{$key}.max"] = "Поле «{$label}»: можно прикрепить не более {$maxFiles} файлов.";

                // Затем валидируем каждый файл внутри массива
                $fileRules = ['file', "max:{$maxSizeKb}"];
                if (!empty($extensions)) {
                    $fileRules[] = 'mimes:' . implode(',', $extensions);
                    $messages["{$key}.*.mimes"] = "Каждый файл в «{$label}» должен быть одного из форматов: "
                        . implode(', ', array_map('strtoupper', $extensions)) . '.';
                }
                $messages["{$key}.*.max"] = "Каждый файл в «{$label}» не должен превышать {$maxSizeMb} МБ.";
                $rules["{$key}.*"] = $fileRules;

            } else {
                // Одиночный файл
                $fileRules   = $isRequired ? ['required', 'file'] : ['nullable', 'file'];
                $fileRules[] = "max:{$maxSizeKb}";
                if (!empty($extensions)) {
                    $fileRules[] = 'mimes:' . implode(',', $extensions);
                    $messages["{$key}.mimes"] = "Файл «{$label}» должен быть одного из форматов: "
                        . implode(', ', array_map('strtoupper', $extensions)) . '.';
                }
                if ($isRequired) {
                    $messages["{$key}.required"] = "Поле «{$label}» обязательно для заполнения.";
                }
                $messages["{$key}.max"] = "Файл «{$label}» не должен превышать {$maxSizeMb} МБ.";
                $rules[$key] = $fileRules;
            }
        }

        if (!empty($rules)) {
            $request->validate($rules, $messages);
        }
    }

    /**
     * Обработка загруженных файлов из полей конструктора
     * Файлы автоматически переименовываются по шаблону:
     * ТипДокумента_Фамилия_ИО_НомерЗаявки.расширение
     */
    private function handleUploadedFilesFromForm(Request $request, Client $client, int $applicationId, ApplicationTemplate $template): void
    {
        $content = $template->content ?? [];

        // Получаем данные для имени файла
        $lastName = $this->sanitizeFilename($client->last_name ?? 'Неизвестно');
        $initials = $this->getInitials($client);
        $appNumber = $applicationId;

        // Счётчик для файлов одного типа (если их несколько)
        $fileCounters = [];

        foreach ($content as $block) {
            if ($block['type'] !== 'file_upload') {
                continue;
            }

            $fieldKey = $block['data']['key'] ?? $block['data']['label'] ?? null;
            if (!$fieldKey) {
                continue;
            }

            $files = $request->file($fieldKey);
            if (!$files) {
                continue;
            }

            // Нормализуем в массив
            $files = is_array($files) ? $files : [$files];

            $fieldLabel = $block['data']['label'] ?? $fieldKey;
            $docType = $this->sanitizeFilename($fieldLabel);

            // Инициализируем счётчик для этого типа документа
            if (!isset($fileCounters[$docType])) {
                $fileCounters[$docType] = 1;
            }

            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    // Формируем новое имя файла
                    $extension = $file->getClientOriginalExtension();
                    $counter = $fileCounters[$docType];
                    
                    // Если файл один - без номера, если несколько - добавляем номер
                    if (count($files) > 1) {
                        $newFileName = "{$docType}_{$lastName}_{$initials}_{$appNumber}_{$counter}.{$extension}";
                    } else {
                        $newFileName = "{$docType}_{$lastName}_{$initials}_{$appNumber}.{$extension}";
                    }

                    // Сохраняем с новым именем
                    $path = $file->storeAs(
                        'client_documents/' . $applicationId,
                        $newFileName,
                        'public'
                    );

                    Document::create([
                        'client_id' => $client->id,
                        'application_id' => $applicationId,
                        'name' => $newFileName,
                        'original_name' => $file->getClientOriginalName(), // Сохраняем оригинальное имя
                        'file_path' => $path,
                        'type' => Document::TYPE_OTHER,
                        'description' => $fieldLabel,
                    ]);

                    $fileCounters[$docType]++;
                }
            }
        }
    }

    /**
     * Получить инициалы клиента (ИО)
     */
    private function getInitials(Client $client): string
    {
        $firstInitial = mb_substr($client->first_name ?? '', 0, 1);
        $middleInitial = mb_substr($client->middle_name ?? '', 0, 1);
        
        $initials = $firstInitial;
        if ($middleInitial) {
            $initials .= $middleInitial;
        }
        
        return $initials ?: 'Н';
    }

    /**
     * Очистка имени файла от недопустимых символов
     */
    private function sanitizeFilename(string $name): string
    {
        // Заменяем пробелы на подчёркивания
        $name = str_replace(' ', '_', $name);
        
        // Заменяем точки на подчёркивания (кроме расширения)
        $name = str_replace('.', '_', $name);
        
        // Убираем множественные подчёркивания
        $name = preg_replace('/_+/', '_', $name);
        
        // Убираем недопустимые символы (оставляем буквы, цифры, подчёркивания, дефисы)
        $name = preg_replace('/[^\p{L}\p{N}_-]/u', '', $name);
        
        // Убираем подчёркивания в начале и конце
        $name = trim($name, '_-');
        
        return $name ?: 'Документ';
    }

    /**
     * Создание/обновление клиента
     */
    private function createClient(array $data, $user, ?Client $existingClient = null): Client
    {
        if ($existingClient) {
            return $existingClient;
        }

        $phone = $data['phone']
            ?? $data['Телефон']
            ?? $data['Контактный телефон']
            ?? null;

        $lastName = $data['last_name'] ?? $data['Фамилия'] ?? null;
        $firstName = $data['first_name'] ?? $data['Имя'] ?? null;
        $middleName = $data['middle_name'] ?? $data['Отчество'] ?? null;

        $companyName = $data['company_name'] ?? $data['Наименование организации'] ?? null;
        $inn = $data['inn'] ?? $data['ИНН'] ?? null;

        return Client::updateOrCreate(
            ['user_id' => $user->id],
            [
                'client_type' => $data['client_type'] ?? 'individual',
                'last_name' => $lastName ?? 'Не указано',
                'first_name' => $firstName ?? 'Не указано',
                'middle_name' => $middleName ?? '',
                'phone' => $phone,
                'email' => $user->email,
                'company_name' => $companyName,
                'inn' => $inn,
            ]
        );
    }

    /**
     * Сборка полного адреса из составных полей
     */
    private function buildFullAddress(array $data): string
    {
        if (!empty($data['address'])) return $data['address'];
        if (!empty($data['Адрес'])) return $data['Адрес'];

        $parts = [];

        $region = $data['region'] ?? $data[' region'] ?? $data['Регион'] ?? null;
        if (!empty($region)) $parts[] = $region;

        if (!empty($data['district'])) $parts[] = $data['district'];
        elseif (!empty($data['Район'])) $parts[] = $data['Район'];

        $locality = $data['locality'] ?? $data['Населенный пункт'] ?? $data['Город'] ?? $data['city'] ?? null;
        if (!empty($locality)) $parts[] = $locality;

        if (!empty($data['street'])) $parts[] = 'ул. ' . $data['street'];
        elseif (!empty($data['Улица'])) $parts[] = 'ул. ' . $data['Улица'];

        if (!empty($data['house'])) $parts[] = 'д. ' . $data['house'];
        elseif (!empty($data['Дом'])) $parts[] = 'д. ' . $data['Дом'];

        if (!empty($data['corpus'])) $parts[] = 'корп. ' . $data['corpus'];
        elseif (!empty($data['Корпус'])) $parts[] = 'корп. ' . $data['Корпус'];
        elseif (!empty($data['building'])) $parts[] = 'корп. ' . $data['building'];

        if (!empty($data['apartment'])) $parts[] = 'кв. ' . $data['apartment'];
        elseif (!empty($data['Квартира'])) $parts[] = 'кв. ' . $data['Квартира'];

        if (empty($parts)) {
            return 'Адрес не указан';
        }

        return implode(', ', $parts);
    }

    /**
     * Генерация PDF документа
     */
    private function generatePdf(array $data, Client $client, string $clientType, $application, $property): string
    {
        $pdfTemplate = PdfTemplate::getTemplate($clientType, PdfTemplate::DOC_APPLICATION);

        $mainInfo = [
            'application_id' => $application->id,
            'full_name' => trim("{$client->last_name} {$client->first_name} {$client->middle_name}"),
            'user_email' => auth()->user()->email,
            'created_at' => now()->format('d.m.Y H:i'),
            'client_type_name' => Client::getClientTypes()[$clientType] ?? $clientType,
            'address' => $property->address,
            'phone' => $client->phone ?? 'Не указан',
            'company_name' => $client->company_name,
            'inn' => $client->inn,
        ];

        // $templateData = array_merge($data, $mainInfo);
        // $templateData['data'] = $templateData;
        $templateData = app(PdfDataPreparator::class)->prepare($data, $mainInfo);

        if ($pdfTemplate) {
            $htmlContent = $pdfTemplate->render($templateData);
        } else {
            $viewName = $clientType === 'legal' ? 'pdf.application_legal' : 'pdf.application_individual';
            $htmlContent = view($viewName, ['data' => $templateData])->render();
        }

        $pdf = Pdf::loadHTML($htmlContent)->setPaper('a4');

        $fileName = 'app_no_'.$application->id.'_'.time().'.pdf';
        $filePath = 'applications/'.$fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }
}