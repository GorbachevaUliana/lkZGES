<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Inertia\Inertia;

class AdminMeterReadingController extends Controller
{
    /**
     * Реестр показаний
     * ИСПРАВЛЕНО: Правильная передача данных клиента и адреса
     */
    public function index()
    {
        $allReadings = MeterReading::with(['property.client.user', 'tariff'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reading) {
                return [
                    'id' => $reading->id,
                    'reading_date' => $reading->reading_date,
                    'current_value' => $reading->current_value,
                    'previous_value' => $reading->previous_value,
                    'total_sum' => $reading->total_sum,
                    'is_paid' => $reading->is_paid,
                    'tariff' => $reading->tariff,
                    // Данные через property
                    'property' => $reading->property ? [
                        'id' => $reading->property->id,
                        'address' => $reading->property->address,
                        'account_number' => $reading->property->account_number,
                    ] : null,
                    // Клиент через property
                    'client' => $reading->property?->client ? [
                        'id' => $reading->property->client->id,
                        'full_name' => $reading->property->client->full_name,
                        'user' => $reading->property->client->user ? [
                            'id' => $reading->property->client->user->id,
                            'name' => $reading->property->client->user->name,
                        ] : null,
                    ] : null,
                ];
            });

        return Inertia::render('Admin/Readings/Readings', [
            'readings' => $allReadings,
        ]);
    }

    public function verifyPayment($id)
    {
        $reading = MeterReading::findOrFail($id);
        $reading->update(['is_paid' => true]);

        return back()->with('success', 'Статус оплаты обновлен');
    }
}