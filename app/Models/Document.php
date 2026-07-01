<?php

namespace App\Models;

use App\Enums\PdfDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель документа
 *
 * Типы документов:
 * - application: Заявка на заключение договора
 * - contract: Договор электроснабжения
 * - other: Другие документы
 */
class Document extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'client_id',
        'application_id',
        'name',
        'original_name',
        'file_path',
        'type',
        'description',
    ];

    /**
     * Атрибуты, которые добавляются при сериализации (accessor'ы)
     */
    protected $appends = [
        'url',
        'type_name',
        'display_name',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Клиент, к которому привязан документ
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Заявка, к которой привязан документ
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Защищённый URL для скачивания документа.
     * Файл отдаётся через DocumentController::serve() с проверкой прав,
     * а не напрямую из /storage/ (там файлы больше не лежат).
     */
    public function getUrlAttribute(): string
    {
        return route('documents.serve', $this->id);
    }

    /**
     * Название типа документа на русском
     */
    public function getTypeNameAttribute(): string
    {
        $types = self::getTypeOptions();

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Отображаемое имя документа (с оригинальным именем в скобках)
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->original_name && $this->original_name !== $this->name) {
            return $this->name . ' (оригинал: ' . $this->original_name . ')';
        }
        return $this->name;
    }

    // ==================== STATIC METHODS ====================

    /**
     * Получить все доступные типы документов
     */
    public static function getTypeOptions(): array
    {
        return PdfDocumentType::labels();
    }

    /**
     * Получить типы для выбора в форме
     */
    public static function getTypesForSelect(): array
    {
        return self::getTypeOptions();
    }

    // ==================== SCOPES ====================

    /**
     * Документы типа "Заявка"
     */
    public function scopeApplications($query)
    {
        return $query->where('type', PdfDocumentType::Application->value);
    }

    /**
     * Документы типа "Договор"
     */
    public function scopeContracts($query)
    {
        return $query->where('type', PdfDocumentType::Contract->value);
    }

    /**
     * Документы типа "Другое"
     */
    public function scopeOther($query)
    {
        return $query->where('type', PdfDocumentType::Other->value);
    }

    // ==================== METHODS ====================

    /**
     * Является ли документ заявкой
     */
    public function isApplication(): bool
    {
        return $this->type === PdfDocumentType::Application->value;
    }

    /**
     * Является ли документ договором
     */
    public function isContract(): bool
    {
        return $this->type === PdfDocumentType::Contract->value;
    }

    /**
     * Является ли документ другим типом
     */
    public function isOther(): bool
    {
        return $this->type === PdfDocumentType::Other->value;
    }
}