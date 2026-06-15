<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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


    // ==================== CONSTANTS ====================

    const TYPE_APPLICATION = 'application';

    const TYPE_CONTRACT = 'contract';

    const TYPE_OTHER = 'other';

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
     * URL для скачивания документа
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->file_path);
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
        return [
            self::TYPE_APPLICATION => 'Заявка',
            self::TYPE_CONTRACT => 'Договор',
            self::TYPE_OTHER => 'Другое',
        ];
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
        return $query->where('type', self::TYPE_APPLICATION);
    }

    /**
     * Документы типа "Договор"
     */
    public function scopeContracts($query)
    {
        return $query->where('type', self::TYPE_CONTRACT);
    }

    /**
     * Документы типа "Другое"
     */
    public function scopeOther($query)
    {
        return $query->where('type', self::TYPE_OTHER);
    }

    // ==================== METHODS ====================

    /**
     * Является ли документ заявкой
     */
    public function isApplication(): bool
    {
        return $this->type === self::TYPE_APPLICATION;
    }

    /**
     * Является ли документ договором
     */
    public function isContract(): bool
    {
        return $this->type === self::TYPE_CONTRACT;
    }

    /**
     * Является ли документ другим типом
     */
    public function isOther(): bool
    {
        return $this->type === self::TYPE_OTHER;
    }
}