<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

/**
 * Личный кабинет — только для потребителей.
 *
 * Сотрудников не пускаем, а перенаправляем в админку: у них там
 * своя работа, а в кабинете они могут случайно завести себе
 * черновик заявки или чужой профиль.
 */
class CheckClient
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ($user->role === UserRole::Admin || $user->role === UserRole::Staff)) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}