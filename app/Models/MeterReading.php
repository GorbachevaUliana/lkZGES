<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeterReading extends Model
{
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
            $property = Property::with('client')->find($reading->property_id);
            $client = $property?->client;

            if ($client) {
                $tariff = Tariff::where('name', $client->tariff_category)
                    ->where('starts_at', '<=', now())
                    ->where(function ($query) {
                        $query->where('ends_at', '>=', now())
                            ->orWhereNull('ends_at');
                    })
                    ->first();

                if ($tariff) {
                    $reading->tariff_id = $tariff->id;
                    $reading->previous_value = self::getLastValue($reading->property_id);
                    $consumed = $reading->current_value - $reading->previous_value;
                    $reading->total_sum = $tariff->calculateCost($consumed);
                } else {
                    $reading->previous_value = self::getLastValue($reading->property_id);
                    $reading->total_sum = 0;
                }
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