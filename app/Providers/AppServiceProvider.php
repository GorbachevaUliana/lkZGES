<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider; // Не забудь импортировать вверху файла
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Определение права "создавать тикеты".
        // Раньше тут была отдельная, более мягкая копия правила (роль client
        // ИЛИ есть одобренная заявка), а настоящая, более строгая версия с
        // проверкой активного объекта жила в User::canCreateTickets() и
        // реально нигде не вызывалась (только из неподключённого middleware).
        // Теперь источник правды один — этот Gate просто делегирует методу модели.
        Gate::define('create-tickets', function (User $user) {
            return $user->canCreateTickets();
        });

        // Твой текущий Inertia Share (оставляем как есть)
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->user()
                        ? auth()->user()->only('id', 'name', 'email', 'role', 'permissions')
                        : null,
                ];
            },
            // ClientLayout решает, показывать ли полное меню, по
            // hasActiveProperties — но раньше этот проп передавал только
            // Client/DashboardController::index(), а не остальные методы
            // (например, documents()). На страницах, которые его не
            // передавали, меню клиента с активным объектом откатывалось к
            // урезанному варианту для заявителя. Через общий Inertia::share()
            // это больше не зависит от того, что именно передал конкретный
            // контроллер.
            //
            // (Раньше эта же правка ошибочно вносилась в
            // App\Http\Middleware\HandleInertiaRequests — этот класс
            // существует в проекте, но нигде не зарегистрирован и реально
            // не выполняется. Правку перенесла сюда, в место, которое
            // приложение действительно использует.)
            'hasActiveProperties' => function () {
                $user = auth()->user();

                if (! $user || $user->role !== \App\Enums\UserRole::Client) {
                    return null;
                }

                $client = $user->client;

                return $client
                    ? $client->properties()
                        ->where('status', 'active')
                        ->whereNotNull('account_number')
                        ->where('account_number', '!=', '')
                        ->exists()
                    : false;
            },
        ]);
    }
}