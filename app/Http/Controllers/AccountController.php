<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Проверяем, есть ли у пользователя привязанный клиент
        $client = $user->client;
        
        if ($client) {
            // Проверяем наличие активных объектов с ЛС
            $hasActiveProperties = $client->properties()
                ->where('status', 'active')
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
                ->exists();
            
            if ($hasActiveProperties) {
                return redirect()->route('client.dashboard');
            }
        }

        return Inertia::render('WelcomePage');
    }

    public function link(Request $request)
    {
        $request->validate([
            'account_number' => 'required',
            'last_name' => 'required',
        ]);

        // Ищем клиента по лицевому счету (через properties) и фамилии
        $client = Client::whereHas('properties', function ($query) use ($request) {
            $query->where('account_number', $request->account_number)
                ->where('status', 'active');
        })->where('last_name', 'ILIKE', $request->last_name)->first();

        if (! $client) {
            return back()->withErrors(['account_number' => 'Клиент с такими данными не найден.']);
        }

        $user = auth()->user();

        // Привязываем клиента к пользователю (client.user_id = user.id)
        $client->update(['user_id' => $user->id]);

        // Обновляем роль пользователя
        $user->update(['role' => 'client']);

        return redirect()->route('client.dashboard');
    }
}
