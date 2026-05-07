<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     */
    public function render(array $data): string
    {
        $content = $this->content;

        // Простая замена переменных вида {{ variable }}
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $content = str_replace('{{ '.$key.' }}', $value ?? '', $content);
            $content = str_replace('{{'.$key.'}}', $value ?? '', $content);
        }

        return $content;
    }
}
