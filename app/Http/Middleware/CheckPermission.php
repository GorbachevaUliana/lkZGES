<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $permission)
    {
        $user = auth()->user();

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->permissions && in_array($permission, $user->permissions)) {
            return $next($request);
        }

        abort(403, 'У вас нет прав для доступа к этому разделу');
    }
}
