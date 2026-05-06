<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCanCreateTickets
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->canCreateTickets()) {
            if ($user->role === 'applicant') {
                return redirect()->route('client.dashboard')
                    ->with('error', 'Функция обращений станет доступна после одобрения вашей заявки на заключение договора.');
            }

            abort(403, 'У вас нет прав для создания обращений.');
        }

        return $next($request);
    }
}
