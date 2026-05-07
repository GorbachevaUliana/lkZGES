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
        'status',
        'tariff_id',
        'client_id',
        'tariff_category',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     *  Объекты клиента
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
            return $this->company_name ?? $this->full_name;
        }

        return $this->full_name;
    }

    /**
     * Полное имя (ФИО)
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->last_name ?? '').' '.
                    ($this->first_name ?? '').' '.
                    ($this->middle_name ?? ''));
    }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    /**
     * Название типа клиента
     */
    public function getClientTypeNameAttribute(): string
    {
        return self::getClientTypes()[$this->client_type] ?? $this->client_type;
    }

    /**
     * Название статуса
     */
    public function getStatusNameAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    // ==================== SCOPES ====================

    /**
     * Активные клиенты
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Неактивные клиенты
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Ожидающие активации
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
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
     * Активен ли клиент
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Активировать клиента (присвоить лицевой счёт)
     */
// App\Models\Client.php

/**
 * Активировать клиента
**/
    public function activate(string $accountNumber): void
    {
        // 1. Убираем обновление status и account_number отсюда, 
        // так как этих колонок нет в таблице clients
        $this->update([
            'updated_at' => now(), 
        ]);

        // 2. Обновляем данные в связанной таблице properties
        // Предполагается, что у клиента есть связь properties()
        $property = $this->properties()->first(); 

        if ($property) {
            $property->update([
                'status' => self::STATUS_ACTIVE, // Убедитесь, что константа STATUS_ACTIVE определена
                'account_number' => $accountNumber
            ]);
        }

        // 3. Обновляем статус пользователя (User), если это необходимо
        if ($this->user) {
            $this->user->update(['status' => 'client']);
        }
    }

    /**
     * Деактивировать клиента
     */
    public function deactivate(): void
    {
        $this->update(['status' => self::STATUS_INACTIVE]);
    }

    /**
     * Привязать пользователя к клиенту
     */
    public function assignUser(User $user): void
    {
        $this->update(['user_id' => $user->id]);
    }

    // public function readings()
    // {
    //     return $this->hasMany(MeterReading::class);
    // }
    public function readings()
    {
        return $this->hasManyThrough(MeterReading::class, Property::class);
    }

    protected $with = ['tariff'];
}
