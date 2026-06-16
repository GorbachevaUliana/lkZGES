<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'account_number',
        'client_type',
        'last_name',
        'first_name',
        'middle_name',
        'company_name',
        'inn',
        'kpp',
        'ogrn',
        'address',
        'phone',
        'email',
        // УБРАНО: tariff_id - тарифы теперь на уровне объектов (properties)
        // УБРАНО: tariff_category - не используется
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'display_name',
        'full_name',
        'client_type_name',
        'status_name',
    ];

    // ==================== CONSTANTS ====================

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_LEGAL = 'legal';

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    const STATUS_PENDING = 'pending';

    // ==================== STATIC METHODS ====================

    /**
     * Получить все типы клиентов
     */
    public static function getClientTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL => 'Физическое лицо',
            self::TYPE_LEGAL => 'Юридическое лицо',
        ];
    }

    /**
     * Получить все статусы
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Активен',
            self::STATUS_INACTIVE => 'Неактивен',
            self::STATUS_PENDING => 'Ожидает активации',
        ];
    }

    /**
     * Получить типы для выпадающего списка
     */
    public static function getClientTypesForSelect(): array
    {
        return self::getClientTypes();
    }

    /**
     * Получить статусы для выпадающего списка
     */
    public static function getStatusesForSelect(): array
    {
        return self::getStatuses();
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Пользователь, привязанный к клиенту
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Заявки клиента
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Документы клиента
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Обращения (тикетсы) клиента
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Объекты клиента (с тарифами)
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    // ==================== ACCESSORS ====================

    /**
     * Отображаемое имя клиента
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->client_type === self::TYPE_LEGAL) {
            return !empty($this->company_name) ? $this->company_name : ($this->full_name ?: 'Название не указано');
        }

        return $this->full_name ?: 'ФИО не указано';
    }

    /**
     * Полное имя (ФИО)
     */
    public function getFullNameAttribute(): string
    {
        $parts = array_filter([
            $this->last_name,
            $this->first_name,
            $this->middle_name
        ]);

        return count($parts) > 0 ? implode(' ', $parts) : '';
    }

    /**
     * Название типа клиента
     */
    public function getClientTypeNameAttribute(): string
    {
        return self::getClientTypes()[$this->client_type] ?? ($this->client_type ?? 'Не указан');
    }

    /**
     * Название статуса - определяется по статусу объектов (properties)
     */
    public function getStatusNameAttribute(): string
    {
        // Если связь properties уже загружена, используем коллекцию, а не новый запрос в БД
        $props = $this->relationLoaded('properties') ? $this->properties : $this->properties();

        $hasActive = $props->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->count() > 0;

        if ($hasActive) return 'Активен';

        $hasPending = $props->where('status', 'pending')->count() > 0;
        if ($hasPending) return 'Ожидает активации';

        return 'Неактивен';
    }

    // ==================== SCOPES ====================

    /**
     * Активные клиенты (есть хотя бы один активный объект)
     */
    public function scopeActive($query)
    {
        return $query->whereHas('properties', function ($q) {
            $q->where('status', self::STATUS_ACTIVE)
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '');
        });
    }

    /**
     * Неактивные клиенты (нет активных объектов)
     */
    public function scopeInactive($query)
    {
        return $query->whereDoesntHave('properties', function ($q) {
            $q->where('status', self::STATUS_ACTIVE)
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '');
        });
    }

    /**
     * Ожидающие активации (есть объекты в ожидании)
     */
    public function scopePending($query)
    {
        return $query->whereHas('properties', function ($q) {
            $q->where('status', self::STATUS_PENDING);
        });
    }

    /**
     * Физические лица
     */
    public function scopeIndividuals($query)
    {
        return $query->where('client_type', self::TYPE_INDIVIDUAL);
    }

    /**
     * Юридические лица
     */
    public function scopeLegal($query)
    {
        return $query->where('client_type', self::TYPE_LEGAL);
    }

    // ==================== METHODS ====================

    /**
     * Является ли физическим лицом
     */
    public function isIndividual(): bool
    {
        return $this->client_type === self::TYPE_INDIVIDUAL;
    }

    /**
     * Является ли юридическим лицом
     */
    public function isLegal(): bool
    {
        return $this->client_type === self::TYPE_LEGAL;
    }

    /**
     * Активен ли клиент (есть активные объекты)
     */
    public function isActive(): bool
    {
        return $this->properties()
            ->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->exists();
    }

    /**
     * Активировать клиента (присвоить лицевой счёт объекту)
     * ИСПРАВЛЕНО: Не обновляем status в clients - колонки не существует!
     * Статус хранится только в таблице properties
     */
    public function activate(string $accountNumber): void
    {
        // Находим свойство, которое нужно активировать
        $property = $this->properties()->latest()->first();

        if ($property) {
            $property->update([
                'status' => self::STATUS_ACTIVE,
                'account_number' => $accountNumber
            ]);
        }

        // Обновляем ROLE пользователя (не status!)
        if ($this->user) {
            $this->user->update(['role' => 'client']);
        }
    }

    /**
     * Деактивировать клиента (деактивировать все объекты)
     */
    public function deactivate(): void
    {
        $this->properties()->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Привязать пользователя к клиенту
     */
    public function assignUser(User $user): void
    {
        $this->update(['user_id' => $user->id]);
    }

    /**
     * Показания через объекты
     */
    public function readings()
    {
        return $this->hasManyThrough(MeterReading::class, Property::class);
    }
}