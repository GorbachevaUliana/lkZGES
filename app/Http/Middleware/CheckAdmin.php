<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ($user->role === UserRole::Admin || $user->role === UserRole::Staff)) {
            return $next($request);
        }

        abort(403, 'У вас нет прав доступа к админ-панели.');
    }
}