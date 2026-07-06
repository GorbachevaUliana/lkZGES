<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PropertyStatus;

class Property extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'client_id',
        'tariff_id',
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
        return $this->status === PropertyStatus::Active->value && !empty($this->account_number);
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
        return $query->where('status', PropertyStatus::Active->value)
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '');
    }
}