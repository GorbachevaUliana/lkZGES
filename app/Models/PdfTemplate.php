<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Sandbox\SecurityPolicy;
use Twig\Source;

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

        $preparedData = [];
        foreach ($data as $key => $value) {
            $preparedData[$key] = $this->normalizeValue($value);
        }

        $preparedData['data'] = $preparedData;

        try {
            return $this->renderWithTwigSandbox($content, $preparedData);
        } catch (\Throwable $e) {
            Log::warning('Twig render failed for template ' . $this->slug . ': ' . $e->getMessage());
            return $this->simpleRender($content, $data);
        }
    }

    private function renderWithTwigSandbox(string $content, array $data): string
    {
        $loader = new ArrayLoader([
            // Имя шаблона = slug, чтобы ошибки были читаемыми.
            (string) ($this->slug ?? 'template') => $content,
        ]);

        $twig = new Environment($loader, [
            // autoescape включён: HTML-символы в данных экранируются автоматически,
            // защита от поломки вёрстки. Шаблон сам использует |raw там, где нужно.
            'autoescape' => 'html',
            'strict_variables' => false,
            'cache' => false,
        ]);
 
        // Whitelist: только безопасные конструкции. methods/properties пустые —
        // вызов методов любых объектов и доступ к их свойствам запрещён.
        $policy = new SecurityPolicy(
            // Разрешённые теги.
            ['if', 'for', 'set', 'block'],
            // Разрешённые фильтры.
            [
                'escape', 'e', 'default', 'upper', 'lower', 'length', 'date',
                'trim', 'join', 'replace', 'split', 'first', 'last', 'abs',
                'number_format', 'round', 'title', 'striptags', 'merge',
                'slice', 'reverse', 'raw', 'format', 'spaceless',
            ],
            // Разрешённые методы (нет).
            [],
            // Разрешённые свойства (нет).
            [],
            // Разрешённые функции.
            ['range', 'min', 'max', 'cycle', 'random', 'date']
        );
 
        $sandbox = new \Twig\Extension\SandboxExtension($policy, true);
        $twig->addExtension($sandbox);
 
        // Source нужен для читаемых ошибок (указывается имя шаблона).
        $twig->parse($twig->tokenize(new Source($content, (string) ($this->slug ?? 'template'))));
 
        return $twig->render((string) ($this->slug ?? 'template'), $data);
    }
 
    /**
     * Привести значение к типу, безопасному для Twig: scalar или массив scalar.
     */
    private function normalizeValue($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'normalizeScalar'], $value);

        }

        return $this->normalizeScalar($value);
    }

    /**
     * Скаляр → строка, объекты с __toString → строка, остальное → ''.
     */
    private function normalizeScalar($value)
    {
        if ($value === null) {
            return '';
        }
        if (is_scalar($value)) {
            return $value;
        }
        if ($value instanceof \Stringable) {
            return (string) $value;
        }
 
        return '';
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