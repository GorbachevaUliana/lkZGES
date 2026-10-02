<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminMeterReadingController extends Controller
{
    /**
     * Реестр показаний
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $like   = MeterReading::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $allReadings = MeterReading::with(['property.client.user', 'tariff'])
            ->when($search !== '', fn ($q) => $q->whereHas('property', fn ($p) => $p
                ->where('account_number', $like, "%{$search}%")
                ->orWhere('address', $like, "%{$search}%")
                ->orWhereHas('client', fn ($c) => $c->where('last_name', $like, "%{$search}%")
                    ->orWhere('first_name', $like, "%{$search}%")
                    ->orWhere('company_name', $like, "%{$search}%"))))
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $allReadings->getCollection()->transform(function ($reading) {
            return [
                'id'             => $reading->id,
                'reading_date'   => $reading->reading_date,
                'current_value'  => $reading->current_value,
                'previous_value' => $reading->previous_value,
                'total_sum'      => $reading->total_sum,
                'is_paid'        => $reading->is_paid,
                'tariff'         => $reading->tariff,
                'property'       => $reading->property ? [
                    'id'             => $reading->property->id,
                    'address'        => $reading->property->address,
                    'account_number' => $reading->property->account_number,
                ] : null,
                'client' => $reading->property?->client ? [
                    'id'        => $reading->property->client->id,
                    'full_name' => $reading->property->client->full_name,
                    'user'      => $reading->property->client->user ? [
                        'id'   => $reading->property->client->user->id,
                        'name' => $reading->property->client->user->name,
                    ] : null,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Readings/Readings', [
            'readings' => $allReadings,
            'search'   => $search,
        ]);
    }

    public function verifyPayment($id)
    {
        $reading = MeterReading::findOrFail($id);
        $reading->update(['is_paid' => true]);
        return back()->with('success', 'Статус оплаты обновлен');
    }
}