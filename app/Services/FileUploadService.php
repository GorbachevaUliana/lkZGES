<?php

namespace App\Services;

use App\Enums\PdfDocumentType;
use App\Models\ApplicationTemplate;
use App\Models\Client;
use App\Models\Document;
use Illuminate\Http\Request;

class FileUploadService
{
    /**
     * Серверная валидация файлов из полей конструктора.
     * Правила строятся динамически из конфига блоков file_upload шаблона.
     * Вызывается ДО транзакции — если упала, ничего не сохраняется.
     */
    public function validateFileUploads(Request $request, ApplicationTemplate $template): void
    {
        $rules    = [];
        $messages = [];

        foreach ($template->content ?? [] as $block) {
            if ($block['type'] !== 'file_upload') {
                continue;
            }

            $key = $block['data']['key'] ?? $block['data']['label'] ?? null;
            if (! $key) {
                continue;
            }

            $label         = $block['data']['label']         ?? $key;
            $isRequired    = (bool)  ($block['data']['is_required']   ?? false);
            $acceptedTypes = (array) ($block['data']['accepted_types'] ?? []);
            $maxSizeMb     = (int)   ($block['data']['max_size']       ?? 10);
            $maxFiles      = (int)   ($block['data']['max_files']      ?? 5);
            $allowMultiple = (bool)  ($block['data']['allow_multiple'] ?? false);

            $extensions = array_keys(array_filter($acceptedTypes));
            $maxSizeKb  = $maxSizeMb * 1024;

            if ($allowMultiple) {
                $arrayRules   = $isRequired ? ['required', 'array'] : ['nullable', 'array'];
                $arrayRules[] = "max:{$maxFiles}";
                $rules[$key]  = $arrayRules;

                if ($isRequired) {
                    $messages["{$key}.required"] = "Поле «{$label}» обязательно для заполнения.";
                }
                $messages["{$key}.max"] = "Поле «{$label}»: можно прикрепить не более {$maxFiles} файлов.";

                $fileRules = ['file', "max:{$maxSizeKb}"];
                if (! empty($extensions)) {
                    $fileRules[]              = 'mimes:' . implode(',', $extensions);
                    $messages["{$key}.*.mimes"] = 'Каждый файл в «' . $label . '» должен быть одного из форматов: '
                        . implode(', ', array_map('strtoupper', $extensions)) . '.';
                }
                $messages["{$key}.*.max"] = "Каждый файл в «{$label}» не должен превышать {$maxSizeMb} МБ.";
                $rules["{$key}.*"]        = $fileRules;
            } else {
                $fileRules   = $isRequired ? ['required', 'file'] : ['nullable', 'file'];
                $fileRules[] = "max:{$maxSizeKb}";
                if (! empty($extensions)) {
                    $fileRules[]           = 'mimes:' . implode(',', $extensions);
                    $messages["{$key}.mimes"] = 'Файл «' . $label . '» должен быть одного из форматов: '
                        . implode(', ', array_map('strtoupper', $extensions)) . '.';
                }
                if ($isRequired) {
                    $messages["{$key}.required"] = "Поле «{$label}» обязательно для заполнения.";
                }
                $messages["{$key}.max"] = "Файл «{$label}» не должен превышать {$maxSizeMb} МБ.";
                $rules[$key]            = $fileRules;
            }
        }

        if (! empty($rules)) {
            $request->validate($rules, $messages);
        }
    }

    /**
     * Сохраняет загруженные файлы из полей конструктора.
     * Имя файла: ТипДокумента_Фамилия_ИО_НомерЗаявки.расширение
     */
    public function handleUploadedFiles(
        Request $request,
        Client $client,
        int $applicationId,
        ApplicationTemplate $template
    ): void {
        $lastName  = $this->sanitizeFilename($client->last_name ?? 'Неизвестно');
        $initials  = $this->getInitials($client);
        $fileCounters = [];

        foreach ($template->content ?? [] as $block) {
            if ($block['type'] !== 'file_upload') {
                continue;
            }

            $fieldKey = $block['data']['key'] ?? $block['data']['label'] ?? null;
            if (! $fieldKey) {
                continue;
            }

            $files = $request->file($fieldKey);
            if (! $files) {
                continue;
            }

            $files      = is_array($files) ? $files : [$files];
            $fieldLabel = $block['data']['label'] ?? $fieldKey;
            $docType    = $this->sanitizeFilename($fieldLabel);

            if (! isset($fileCounters[$docType])) {
                $fileCounters[$docType] = 1;
            }

            foreach ($files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $extension   = $file->getClientOriginalExtension();
                $counter     = $fileCounters[$docType];
                $newFileName = count($files) > 1
                    ? "{$docType}_{$lastName}_{$initials}_{$applicationId}_{$counter}.{$extension}"
                    : "{$docType}_{$lastName}_{$initials}_{$applicationId}.{$extension}";

                $path = $file->storeAs(
                    'client_documents/' . $applicationId,
                    $newFileName,
                    'local'
                );

                Document::create([
                    'client_id'      => $client->id,
                    'application_id' => $applicationId,
                    'name'           => $newFileName,
                    'original_name'  => $file->getClientOriginalName(),
                    'file_path'      => $path,
                    'type'           => PdfDocumentType::Other->value,
                    'description'    => $fieldLabel,
                ]);

                $fileCounters[$docType]++;
            }
        }
    }

    /**
     * Инициалы клиента (ИО) для именования файлов.
     */
    public function getInitials(Client $client): string
    {
        $first  = mb_substr($client->first_name  ?? '', 0, 1);
        $middle = mb_substr($client->middle_name ?? '', 0, 1);

        return ($first . $middle) ?: 'Н';
    }

    /**
     * Очистка строки для использования в имени файла.
     */
    public function sanitizeFilename(string $name): string
    {
        $name = str_replace([' ', '.'], '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = preg_replace('/[^\p{L}\p{N}_-]/u', '', $name);
        $name = trim($name, '_-');

        return $name ?: 'Документ';
    }
}