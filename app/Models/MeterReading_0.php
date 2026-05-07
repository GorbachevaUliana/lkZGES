<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    protected $fillable = [
        // 'client_id',
        'tariff_id',
        'previous_value',
        'current_value',
        'total_sum',
        'reading_date',
        'is_paid',
        'created_by',
        'property_id',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'is_paid' => 'boolean',
        'total_sum' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    // public function client(): BelongsTo
    // {
    //     return $this->belongsTo(Client::class);
    // }

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function property(): BelongsTo
    {   
        return $this->belongsTo(Property::class);
    }

    // ==================== LOGIC ====================

    /**
     * Автоматический расчет при создании записи
     */
    protected static function booted()
    {
        static::creating(function ($reading) {
            // Ищем клиента через объект недвижимости (property)
            $property = Property::with('client')->find($reading->property_id);
            $client = $property?->client;

            if ($client) {
                // Берем тариф напрямую у клиента или из категории
                $tariff = $client->tariff; // У тебя в Client.php есть связь tariff()

                if ($tariff) {
                    $reading->tariff_id = $tariff->id;
                    // Получаем последнее значение по property_id, а не по клиенту!
                    $reading->previous_value = self::getLastValue($reading->property_id);
                    $consumed = $reading->current_value - $reading->previous_value;
                    $reading->total_sum = $tariff->calculateCost($consumed);
                }
            }
        });
    }

    // Поиск последнего значения теперь по объекту (адресу)
    public static function getLastValue($propertyId)
    {
        return self::where('property_id', $propertyId)
            ->latest('reading_date')
            ->latest('id')
            ->value('current_value') ?? 0;
    }
}
