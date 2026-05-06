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
        // Определение права "создавать тикеты"
        Gate::define('create-tickets', function (User $user) {
            // Проверяем: либо у юзера роль клиента,
            // либо у него есть хотя бы одна одобренная заявка
            return $user->role === 'client' ||
                $user->applications()->where('status', 'approved')->exists();
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
