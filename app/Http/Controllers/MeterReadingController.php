<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeterReadingController extends Controller
{
    /**
     * Страница показаний
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
                ->first();
        } else {
            $property = $client->properties()
                ->where('status', 'active')
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
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
            ->get();

        $currentTariff = Tariff::where('name', $client->tariff_category)
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->where('ends_at', '>=', now())
                    ->orWhereNull('ends_at');
            })
            ->first();

        $lastReadingValue = MeterReading::getLastValue($property->id);

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
     */
    public function storeReading(Request $request)
    {
        $client = auth()->user()->client;

        if (! $client) {
            return back()->withErrors(['error' => 'Профиль клиента не связан с вашим аккаунтом.']);
        }

        $validated = $request->validate([
            'current_value' => 'required|integer|min:0',
            'reading_date' => 'required|date',
            'property_id' => 'required|integer|exists:properties,id',
        ]);

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

        $property->readings()->create([
            'current_value' => $validated['current_value'],
            'reading_date' => $validated['reading_date'],
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