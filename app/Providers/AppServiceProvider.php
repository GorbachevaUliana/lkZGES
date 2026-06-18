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
        ]);
    }
}