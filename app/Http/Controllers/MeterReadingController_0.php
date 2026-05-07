<?php

namespace App\Http\Controllers;

use App\Models\MeterReading;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeterReadingController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $client = $user->client;

        // ЗАЩИТА: Если это сотрудник без привязанного ЛС
        if (! $client) {
            // Если это админ, мы можем либо редиректнуть его в админку,
            // либо показать пустую страницу с уведомлением.
            if ($user->role === 'admin' || $user->role === 'staff') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'У сотрудников нет личного кабинета потребителя.');
            }

            // Если это обычный юзер, который почему-то не привязан к клиенту
            return redirect()->route('welcome.step');
        }

        $currentTariff = Tariff::where('name', $client->tariff_category)
            ->where('starts_at', '<=', now())
            ->where(function ($query) {
                $query->where('ends_at', '>=', now())
                    ->orWhereNull('ends_at');
            })
            ->first();

        $lastReadingValue = MeterReading::getLastValue($client->id);

        $history = $client->readings()
            ->with('tariff')
            ->orderBy('reading_date', 'desc')
            ->take(12)
            ->get();

        return Inertia::render('Client/Readings/Readings', [
            'client' => $client,
            'currentTariff' => $currentTariff,
            'lastReadingValue' => $lastReadingValue,
            'history' => $history,
        ]);
    }

    public function storeReading(Request $request)
    {
        // 1. Берем клиента текущего юзера
        $client = auth()->user()->client;

        if (! $client) {
            return back()->withErrors(['error' => 'Профиль клиента не связан с вашим аккаунтом.']);
        }

        // 2. Валидация
        $validated = $request->validate([
            'current_value' => 'required|integer|min:0',
            'reading_date' => 'required|date',
        ]);

        // 3. Проверка на уменьшение показаний (логическая защита)
        $previousValue = MeterReading::getLastValue($client->id);
        if ($validated['current_value'] < $previousValue) {
            return back()->withErrors(['current_value' => "Показания не могут быть меньше предыдущих ($previousValue)"]);
        }
        // client_id подставится автоматически самой Laravel
        $client->readings()->create([
            'current_value' => $validated['current_value'],
            'reading_date' => $validated['reading_date'],
            'created_by' => auth()->id(),
            // Остальное (total_sum, tariff_id и т.д.) посчитает модель в booted()
        ]);

        return back()->with('success', 'Показания приняты');
    }

    public function pay($id)
    {
        $reading = MeterReading::findOrFail($id);

        // Проверка: может ли этот юзер оплатить эти показания
        if ($reading->client_id !== auth()->user()->client->id) {
            abort(403);
        }

        $reading->update(['is_paid' => true]);

        return back()->with('success', 'Счет успешно оплачен!');
    }
}
