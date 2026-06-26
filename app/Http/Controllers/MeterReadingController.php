<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Http\Request;
use App\Http\Requests\Client\StoreMeterReadingRequest;
use Inertia\Inertia;

class MeterReadingController extends Controller
{
    /**
     * Страница показаний
     *
     * ВАЖНО: Тариф берётся из объекта (property), а не из клиента!
     * Каждый объект имеет свой тариф.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $client = $user->client;

        if (! $client) {
            if ($user->role === 'admin' || $user->role === 'staff') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'У сотрудников нет личного кабинета потребителя.');
            }
            return redirect()->route('welcome.step');
        }

        $propertyId = $request->query('property');

        if ($propertyId) {
            $property = Property::where('id', $propertyId)
                ->where('client_id', $client->id)
                ->with('tariff')
                ->first();
        } else {
            $property = $client->properties()
                ->where('status', 'active')
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
                ->with('tariff')
                ->first();
        }

        if (!$property) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Объект не найден.');
        }

        $activeProperties = $client->properties()
            ->where('status', 'active')
            ->whereNotNull('account_number')
            ->where('account_number', '!=', '')
            ->with('tariff')
            ->get();

        // ИСПРАВЛЕНО: Тариф берётся из объекта (property), а не из клиента!
        $currentTariff = $property->tariff;

        // Если у объекта нет тарифа - пытаемся найти по названию (для обратной совместимости)
        if (!$currentTariff && $property->tariff_id) {
            $currentTariff = Tariff::find($property->tariff_id);
        }

        $lastReadingValue = MeterReading::getLastValue($property->id);

        // ИСПРАВЛЕНО: История показаний только для конкретного объекта!
        $history = MeterReading::where('property_id', $property->id)
            ->with('tariff')
            ->orderBy('reading_date', 'desc')
            ->take(12)
            ->get();

        return Inertia::render('Client/Readings/Readings', [
            'client' => $client,
            'property' => $property,
            'activeProperties' => $activeProperties,
            'currentTariff' => $currentTariff,
            'lastReadingValue' => $lastReadingValue,
            'history' => $history,
        ]);
    }

    /**
     * Сохранение показаний
     *
     * Показания привязаны к конкретному объекту (property)
     */
    public function storeReading(StoreMeterReadingRequest $request)
    {
        $client = auth()->user()->client;

        if (! $client) {
            return back()->withErrors(['error' => 'Профиль клиента не связан с вашим аккаунтом.']);
        }

        $validated = $request->validated();

        $property = Property::where('id', $validated['property_id'])
            ->where('client_id', $client->id)
            ->first();

        if (!$property) {
            return back()->withErrors(['property_id' => 'Объект не найден или не принадлежит вам.']);
        }

        $previousValue = MeterReading::getLastValue($validated['property_id']);
        if ($validated['current_value'] < $previousValue) {
            return back()->withErrors(['current_value' => "Показания не могут быть меньше предыдущих ($previousValue)"]);
        }

        // Создаём показания с привязкой к объекту и его тарифу
        $property->readings()->create([
            'current_value' => $validated['current_value'],
            'reading_date' => $validated['reading_date'],
            'tariff_id' => $property->tariff_id, // Тариф из объекта
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Показания приняты');
    }

    public function pay($id)
    {
        $reading = MeterReading::findOrFail($id);

        $user = auth()->user();
        if ($reading->property->client_id !== $user->client?->id) {
            abort(403);
        }

        $reading->update(['is_paid' => true]);

        return back()->with('success', 'Счет успешно оплачен!');
    }
}