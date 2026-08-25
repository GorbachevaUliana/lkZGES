<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $shared = [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    // Раньше не передавалось вообще — AdminLayout читает
                    // user.permissions для показа пунктов меню сотруднику
                    // без роли admin, поэтому у любого сотрудника без роли
                    // "Администратор" меню было пустым независимо от того,
                    // какие права ему реально назначены.
                    'permissions' => $user->permissions,
                ] : null,
            ],
        ];

        // ClientLayout определяет, показывать ли полное меню, по
        // hasActiveProperties/properties — но раньше эти пропсы передавал
        // только Client/DashboardController::index(), а не остальные
        // методы (например, documents()). На страницах, которые их не
        // передавали, меню клиента с активным объектом откатывалось к
        // урезанному варианту для заявителя. Теперь это расшарено глобально
        // и не зависит от того, что именно передал конкретный контроллер.
        if ($user && $user->role === \App\Enums\UserRole::Client) {
            $client = $user->client;
            $hasActiveProperties = $client
                ? $client->properties()
                    ->where('status', 'active')
                    ->whereNotNull('account_number')
                    ->where('account_number', '!=', '')
                    ->exists()
                : false;

            $shared['hasActiveProperties'] = $hasActiveProperties;
        }

        return $shared;
    }
}