<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

/**
 * Модель PDF-шаблона
 *
 * Хранит HTML/Blade шаблоны для генерации PDF-документов.
 * Администраторы могут редактировать шаблоны через Filament.
 *
 * client_type: individual | legal
 * document_type: application | contract | other
 */
class PdfTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'client_type',
        'document_type',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_LEGAL = 'legal';

    const DOC_APPLICATION = 'application';

    const DOC_CONTRACT = 'contract';

    const DOC_OTHER = 'other';

    public static function getClientTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL => 'Физическое лицо',
            self::TYPE_LEGAL => 'Юридическое лицо',
        ];
    }

    public static function getDocumentTypes(): array
    {
        return [
            self::DOC_APPLICATION => 'Заявка',
            self::DOC_CONTRACT => 'Договор',
            self::DOC_OTHER => 'Другое',
        ];
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Шаблоны заявок, использующие этот PDF-шаблон
     */
    public function applicationTemplates()
    {
        return $this->hasMany(ApplicationTemplate::class, 'pdf_template_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForIndividuals($query)
    {
        return $query->where('client_type', self::TYPE_INDIVIDUAL);
    }

    public function scopeForLegal($query)
    {
        return $query->where('client_type', self::TYPE_LEGAL);
    }

    public function scopeApplications($query)
    {
        return $query->where('document_type', self::DOC_APPLICATION);
    }

    public function scopeContracts($query)
    {
        return $query->where('document_type', self::DOC_CONTRACT);
    }

    // ==================== METHODS ====================

    /**
     * Получить шаблон по типу клиента и документа
     */
    public static function getTemplate(string $clientType, string $documentType): ?self
    {
        return static::active()
            ->where('client_type', $clientType)
            ->where('document_type', $documentType)
            ->first();
    }

    /**
     * Рендеринг шаблона с данными
     * Поддерживает Blade-синтаксис: {{ $var }}, @if, @foreach, @php и т.д.
     */
    public function render(array $data): string
    {
        $content = $this->content;

        // Подготавливаем данные - все значения должны быть scalar или иметь __toString
        $preparedData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $preparedData[$key] = $value; // Сохраняем массивы для $data['ключ']
            } elseif (is_object($value) && method_exists($value, '__toString')) {
                $preparedData[$key] = (string) $value;
            } else {
                $preparedData[$key] = $value ?? '';
            }
        }

        // Добавляем $data как массив для доступа $data['ключ']
        $preparedData['data'] = $preparedData;

        // Проверяем, есть ли Blade-директивы в шаблоне
        $hasBladeDirectives = preg_match('/@\w+|{{.*?}}/', $content);

        if ($hasBladeDirectives) {
            try {
                // Компилируем Blade-шаблон
                $compiled = Blade::compileString($content);

                // Создаем замыкание для рендеринга
                $render = function ($__compiled, $__data) {
                    ob_start();
                    extract($__data, EXTR_SKIP);
                    eval('?>' . $__compiled);
                    return ob_get_clean();
                };

                return $render($compiled, $preparedData);
            } catch (\Exception $e) {
                // Если Blade рендеринг не удался - используем простую замену
                \Log::warning('Blade render failed for template ' . $this->slug . ': ' . $e->getMessage());
                return $this->simpleRender($content, $data);
            }
        }

        // Простая замена переменных
        return $this->simpleRender($content, $data);
    }

    /**
     * Простая замена переменных вида {{ variable }}
     */
    private function simpleRender(string $content, array $data): string
    {
        // Замена простых переменных {{ $var }}
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue; // Пропускаем массивы в простом рендеринге
            }
            $content = str_replace('{{ $'.$key.' }}', $value ?? '', $content);
            $content = str_replace('{{'.$key.'}}', $value ?? '', $content);
            $content = str_replace('{{ '.$key.' }}', $value ?? '', $content);
        }

        // Замена переменных массива {{ $data['key'] }}
        if (isset($data['data']) && is_array($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $content = str_replace("{{ \$data['".$key."'] }}", $value ?? '', $content);
                $content = str_replace('{{ $data[\''.$key.'\'] }}', $value ?? '', $content);
            }
        }

        return $content;
    }

    /**
     * Получить дефолтный шаблон из файла
     */
    public static function getDefaultTemplate(string $clientType): ?string
    {
        $viewName = $clientType === 'legal' ? 'pdf.application_legal' : 'pdf.application_individual';

        try {
            return view($viewName, ['data' => []])->render();
        } catch (\Exception $e) {
            return null;
        }
    }
}
