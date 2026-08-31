<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Application;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

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
                    'permissions' => $user->permissions,
                ] : null,
            ],
        ];

        if ($user && $user->role === UserRole::Client) {
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

        if ($user) {
            $draft = Application::where('user_id', $user->id)
                ->where('status', ApplicationStatus::Draft->value)
                ->first();

            $shared['draft'] = $draft ? [
                'id' => $draft->id,
                'client_type' => $draft->client_type,
                'status' => $draft->status,
            ] : null;
        }

        return $shared;
    }
}