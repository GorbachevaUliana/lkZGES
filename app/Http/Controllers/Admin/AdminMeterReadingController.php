<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Inertia\Inertia;

class AdminMeterReadingController extends Controller
{
    public function index()
    {
        $allReadings = MeterReading::with(['property.client.user', 'tariff'])
            ->orderBy('created_at', 'desc')
            ->get();
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
