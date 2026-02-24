<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Проверяем роль: пускаем и админа, и оператора (staff)
        if ($user && ($user->role === 'admin' || $user->role === 'staff')) {
            return $next($request);
        }

        // Если это не сотрудник, просто выкидываем ошибку 403, 
        // а не редиректим обратно на логин!
        abort(403, 'У вас нет прав доступа к админ-панели.');
    }
}