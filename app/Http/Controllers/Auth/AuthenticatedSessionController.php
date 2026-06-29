<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use App\Enums\UserRole;

class AuthenticatedSessionController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        
        // Админы и сотрудники идут в админ-панель
        if ($user->role === UserRole::Admin || $user->role === UserRole::Staff) {
            return redirect()->intended(route('admin.clients.index', absolute: false));
        }

        // Проверяем наличие привязанного клиента через связь
        $client = $user->client;
        
        if ($client) {
            // Проверяем наличие активных объектов
            $hasActiveProperties = $client->properties()
                ->where('status', 'active')
                ->whereNotNull('account_number')
                ->where('account_number', '!=', '')
                ->exists();
            
            if ($hasActiveProperties) {
                return redirect()->intended(route('client.dashboard', absolute: false));
            }
            
            // Есть клиент, но нет активных объектов - в профиль
            return redirect()->intended(route('client.profile', absolute: false));
        }

        // Нет клиента - на страницу выбора
        return redirect()->intended(route('welcome.step', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
