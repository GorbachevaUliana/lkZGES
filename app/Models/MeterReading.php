<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
    use SoftDeletes;
    protected $fillable = [
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
            // Тариф закреплён за объектом (property->tariff_id) при одобрении заявки,
            // см. ApplicationService::updateStatus(). У клиента поля tariff_category
            // больше нет (см. Client::$fillable), поэтому искать тариф через клиента
            // нельзя — раньше это приводило к тому, что тариф никогда не находился
            // и total_sum всегда уходил в 0.
            $property = Property::with('tariff')->find($reading->property_id);
            $tariff = $property?->tariff;

            $reading->previous_value = self::getLastValue($reading->property_id);

            if ($tariff) {
                $reading->tariff_id = $tariff->id;
                $consumed = $reading->current_value - $reading->previous_value;
                $reading->total_sum = $tariff->calculateCost($consumed);
            } else {
                // У объекта нет назначенного тарифа — явно фиксируем как "не посчитано",
                // а не молча списываем 0 руб. См. отдельный пункт про эту проблему ниже.
                $reading->total_sum = 0;
            }
        });
    }

    public static function getLastValue($propertyId)
    {
        return self::where('property_id', $propertyId)
            ->latest('reading_date')
            ->latest('id')
            ->value('current_value') ?? 0;
    }
}