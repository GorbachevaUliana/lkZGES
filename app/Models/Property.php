<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'tariff_id',  // ДОБАВЛЕНО
        'account_number',
        'address',
        'property_type',
        'area',
        'status',
        'meter_number',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Статусы объекта
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Связь с клиентом
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Связь с тарифом
     */
    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    /**
     * Связь с заявками
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Связь с показаниями счетчиков
     */
    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * Алиас для meterReadings
     */
    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }

    /**
     * Связь с платежами
     */
    // public function payments()
    // {
    //     return $this->hasMany(Payment::class);
    // }

    /**
     * Проверка активности объекта
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !empty($this->account_number);
    }

    /**
     * Получить последнее показание счетчика
     */
    public function getLastReadingAttribute(): ?MeterReading
    {
        return $this->meterReadings()->latest('reading_date')->first();
    }

    /**
     * Scope для активных объектов с ЛС
     */
    public function scopeActiveWithAccount($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '');
    }
}